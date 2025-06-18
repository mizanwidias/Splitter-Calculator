@extends('layouts.app')

@section('title', 'Topologi FTTH')

@section('content')
<div class="row">
    <div class="col-md-3">
        <h5>Tambah Perangkat</h5>
        <div class="mb-2">
            <label>Power OLT (dBm)</label>
            <input type="number" id="input-power" class="form-control form-control-sm" value="7">
        </div>
        <button class="btn btn-sm btn-primary mb-2 w-100" onclick="addNode('OLT')">+ OLT</button>
        <button class="btn btn-sm btn-primary mb-2 w-100" onclick="addNode('Splitter')">+ Splitter</button>
        <button class="btn btn-sm btn-primary mb-2 w-100" onclick="addNode('ODP')">+ ODP</button>
        <button class="btn btn-sm btn-primary mb-2 w-100" onclick="addNode('Client')">+ Client</button>
        <hr>
        <div class="mb-2">
            <label>Jenis Kabel</label>
            <div class="d-flex gap-2" id="cable-options">
                <div onclick="selectCable('0.2', 'dropcore', this)" class="cable-option p-2 border rounded text-center cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: black; margin: auto;"></div>
                    <small>Dropcore</small>
                </div>
                <div onclick="selectCable('0.3', 'patchcord', this)" class="cable-option p-2 border rounded text-center cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: yellow; margin: auto;"></div>
                    <small>Patchcord</small>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <label>Panjang Kabel (m)</label>
            <input type="number" id="cable-length" class="form-control form-control-sm" value="50">
        </div>
        <div class="mb-2">
            <label>Total Connector</label>
            <input type="number" id="connectors" class="form-control form-control-sm" value="2">
        </div>
        <div class="mb-2">
            <label>Total Splicing (0,1dB)</label>
            <input type="number" id="splicing" class="form-control form-control-sm" value="1">
        </div>
        <button class="btn btn-sm btn-info mb-2 w-100" onclick="submitTopology()">💾 Save Topologi</button>
        <button class="btn btn-sm btn-danger mb-2 w-100" onclick="undoAction()">↩ Undo</button>
        <button class="btn btn-sm btn-warning mb-2 w-100" onclick="resetMap()">Reset</button>
        <button class="btn btn-sm btn-success w-100" onclick="saveTopology()">Hitung Loss</button>
    </div>

    <div class="col-md-6 position-relative" id="map-canvas" style="min-height: 500px; background-color: #f9f9f9; border: 1px solid #ccc;"></div>

    <div class="modal fade" id="splitterModal" tabindex="-1" aria-labelledby="splitterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Tipe Splitter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <select id="splitterTypeSelect" class="form-select">
                        <option value="1:2">1:2</option>
                        <option value="1:4">1:4</option>
                        <option value="1:8">1:8</option>
                        <option value="1:16">1:16</option>
                        <option value="1:32">1:32</option>
                        <option value="1:64">1:64</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="confirmSplitter()">Tambah Splitter</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <h5>Informasi Loss</h5>
        <div id="info-card" class="card d-none">
            <div class="card-body">
                <h6 class="card-title">Total Loss</h6>
                <p class="card-text"><strong id="total-loss">-</strong> dB</p>
                <p class="card-text">Output Power: <strong id="power-rx">-</strong> dBm</p>
                <p class="card-text">Jalur: <span id="jalur-text">-</span></p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leader-line/1.0.7/leader-line.min.js"></script>
<script>
    let nodes = [],
        lines = [],
        nodeId = 0,
        selectedNode = null,
        pendingSplitter = false;

    let selectedCableLoss = 0.2 / 1000;
    let selectedCableColor = 'black';
    let selectedCableName = 'Dropcore';

    function selectCable(lossPerKM, type, el) {
        selectedCableLoss = parseFloat(lossPerKM) / 1000;
        selectedCableColor = type === 'patchcord' ? 'yellow' : 'black';
        selectedCableName = type.charAt(0).toUpperCase() + type.slice(1);
        document.querySelectorAll('.cable-option').forEach(opt => opt.classList.remove('selected'));
        el.classList.add('selected');
    }

    function makeDraggable(el) {
        let isDragging = false,
            offsetX, offsetY;
        el.addEventListener('mousedown', function(e) {
            isDragging = true;
            offsetX = e.offsetX;
            offsetY = e.offsetY;
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        function onMouseMove(e) {
            if (!isDragging) return;
            const canvas = document.getElementById('map-canvas');
            const canvasRect = canvas.getBoundingClientRect();
            el.style.left = (e.clientX - canvasRect.left - offsetX) + 'px';
            el.style.top = (e.clientY - canvasRect.top - offsetY) + 'px';
            lines.forEach(link => {
                if (link.from === el.id || link.to === el.id) {
                    link.line.position();
                }
            });
        }

        function onMouseUp() {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }
    }

    function addNode(type) {
        if (type === 'Splitter') {
            pendingSplitter = true;
            new bootstrap.Modal(document.getElementById('splitterModal')).show();
            return;
        }

        const el = document.createElement("div");
        el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
        el.style.top = "100px";
        el.style.left = "100px";
        el.setAttribute("id", `node-${nodeId}`);

        let label = type;
        let loss = 0;

        if (type.startsWith('Splitter')) {
            const splitRatio = type.split(' ')[1];
            const splitLoss = {
                '1:2': 3.5,
                '1:4': 7.2,
                '1:8': 10.5,
                '1:16': 13.5,
                '1:32': 17.0,
                '1:64': 20.5,
            };
            loss = splitLoss[splitRatio] || 0;
        }

        if (type === 'ODP') {
            const odpType = prompt("Masukkan jenis ODP (Mini/Besar)");
            label = `ODP ${odpType}`;
            el.dataset.odp = odpType;
            loss = odpType.toLowerCase() === 'besar' ? 0.5 : 0.2;
        }

        el.innerHTML = `<strong>${label}</strong><div class="output-power" style="font-size: 12px; color: green;"></div>`;
        el.dataset.loss = loss;
        el.dataset.power = "";

        el.addEventListener('click', () => {
            if (!selectedNode) {
                selectedNode = el;
                el.classList.add('border-primary');
            } else if (selectedNode !== el) {
                const length = parseFloat(prompt("Masukkan panjang kabel (meter):", document.getElementById("cable-length").value));
                if (!isNaN(length)) {
                    connectNodeElements(selectedNode, el, length);
                }
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            } else {
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            }
            // Klik kanan untuk hapus
            el.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                if (confirm('Hapus node ini beserta kabel yang terhubung?')) {
                    deleteNode(el);
                }
            });
        });

        makeDraggable(el);
        document.getElementById("map-canvas").appendChild(el);
        nodeId++;
    }

    function addLineContextMenu(lineObj) {
        const lineEl = lineObj.line;
        lineEl.svg.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (confirm('Hapus kabel ini?')) {
                lineEl.remove();
                lines = lines.filter(l => l !== lineObj);
            }
        });
    }


    function confirmSplitter() {
        const splitterType = document.getElementById('splitterTypeSelect').value;
        bootstrap.Modal.getInstance(document.getElementById('splitterModal')).hide();
        addNode(`Splitter ${splitterType}`);
    }

    function connectNodeElements(source, target, length) {
        const lossCable = length * selectedCableLoss;
        const lossTarget = parseFloat(target.dataset.loss || 0);
        const totalConnectors = parseInt(document.getElementById("connectors").value || 0);
        const totalSplicing = parseInt(document.getElementById("splicing").value || 0);
        const connectorLoss = totalConnectors * 0.2;
        const splicingLoss = totalSplicing * 0.1;
        const totalLoss = lossCable + lossTarget + connectorLoss + splicingLoss;

        const sourcePower = parseFloat(source.dataset.power || document.getElementById("input-power").value || 0);
        const powerRx = sourcePower - totalLoss;
        target.dataset.power = powerRx.toFixed(2);
        target.querySelector(".output-power").innerText = `${powerRx.toFixed(2)} dB`;

        const line = new LeaderLine(
            LeaderLine.pointAnchor(source, {
                x: '50%',
                y: '50%'
            }),
            LeaderLine.pointAnchor(target, {
                x: '50%',
                y: '50%'
            }), {
                color: selectedCableColor,
                size: 2,
                path: 'straight',
                startPlug: 'none',
                endPlug: 'none',
                dash: {
                    animation: true
                },
                middleLabel: LeaderLine.pathLabel(`-${lossCable.toFixed(2)} dB`, {
                    color: 'red',
                    fontSize: '12px'
                }),
                startLabel: selectedCableName
            }
        );

        lines.push({
            from: source.id,
            to: target.id,
            cable: selectedCableName,
            line
        });

        // Update info panel
        document.getElementById("info-card").classList.remove("d-none");
        document.getElementById("total-loss").innerText = totalLoss.toFixed(2);
        document.getElementById("power-rx").innerText = powerRx.toFixed(2);
        document.getElementById("jalur-text").innerText = `${source.querySelector('strong').innerText} → ${target.querySelector('strong').innerText}`;

        addLineContextMenu({
            from: source.id,
            to: target.id,
            cable: selectedCableName,
            line
        });
    }

    function undoAction() {
        if (lines.length > 0) {
            const last = lines.pop();
            last.line.remove();
        }
    }

    function resetMap() {
        // Hapus semua garis
        lines.forEach(link => link.line.remove());
        lines = [];

        // Hapus semua node
        const canvas = document.getElementById("map-canvas");
        canvas.innerHTML = '';

        // Reset node ID
        nodeId = 0;
        selectedNode = null;

        // Sembunyikan info
        document.getElementById("info-card").classList.add("d-none");
    }

    function saveTopology() {
        const topology = {
            nodes: [],
            connections: []
        };

        nodes = Array.from(document.querySelectorAll("#map-canvas > div"));

        nodes.forEach(node => {
            topology.nodes.push({
                id: node.id,
                type: node.querySelector('strong')?.innerText || '',
                loss: parseFloat(node.dataset.loss || 0),
                power: parseFloat(node.dataset.power || 0),
                top: node.style.top,
                left: node.style.left,
            });
        });

        lines.forEach(conn => {
            topology.connections.push({
                from: conn.from,
                to: conn.to,
                cable: conn.cable
            });
        });

        function deleteNode(el) {
            // Hapus koneksi yang terhubung ke node
            lines = lines.filter(conn => {
                if (conn.from === el.id || conn.to === el.id) {
                    conn.line.remove();
                    return false;
                }
                return true;
            });

            // Hapus elemen node
            el.remove();
        }

        function submitTopology() {
            const data = {
                nodes: [],
                connections: []
            };

            const mapCanvas = document.getElementById("map-canvas");
            const allNodes = mapCanvas.querySelectorAll("div[id^='node-']");

            allNodes.forEach(node => {
                data.nodes.push({
                    id: node.id,
                    type: node.innerText.split('\n')[0],
                    loss: parseFloat(node.dataset.loss || 0),
                    power: parseFloat(node.dataset.power || 0),
                    top: node.style.top,
                    left: node.style.left,
                });
            });

            lines.forEach(line => {
                data.connections.push({
                    from: line.from,
                    to: line.to,
                    cable: line.cable
                });
            });

            fetch('/simpan-topologi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                }).then(res => res.json())
                .then(res => alert('✅ Topologi berhasil disimpan!'))
                .catch(err => alert('❌ Gagal menyimpan topologi.'));
        }

        // Untuk sekarang, tampilkan di konsol
        console.log("SAVED TOPOLOGY:", topology);

        // Jika mau kirim ke server Laravel:
        // fetch('/simpan-topologi', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        //     },
        //     body: JSON.stringify(topology)
        // }).then(res => res.json())
        //   .then(data => alert('Berhasil disimpan!'))
        //   .catch(err => alert('Gagal simpan'));
    }
</script>
@endpush