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
            <select id="cable-type" class="form-control form-control-sm">
                <option value="0.2">Dropcore (0.2 dB/km)</option>
                <option value="0.35">Patchcord (0.35 dB/km)</option>
            </select>
        </div>
        <div class="mb-2">
            <label>Panjang Kabel (m)</label>
            <input type="number" id="cable-length" class="form-control form-control-sm" value="50">
        </div>
        <div class="mb-2">
            <label>Jumlah Konektor</label>
            <input type="number" id="connectors" class="form-control form-control-sm" value="2">
        </div>
        <div class="mb-2">
            <label>Jumlah Splicing</label>
            <input type="number" id="splicing" class="form-control form-control-sm" value="1">
        </div>
        <button class="btn btn-sm btn-secondary mb-2 w-100" onclick="connectNodes()">Sambungkan Node</button>
        <button class="btn btn-sm btn-warning mb-2 w-100" onclick="resetMap()">Reset</button>
        <button class="btn btn-sm btn-success w-100" onclick="saveTopology()">Hitung Loss</button>
    </div>

    <div class="col-md-6 position-relative" id="map-canvas" style="min-height: 500px; background-color: #f9f9f9; border: 1px solid #ccc;"></div>

    <div class="col-md-3">
        <h5>Informasi Loss</h5>
        <div id="info-card" class="card d-none">
            <div class="card-body">
                <h6 class="card-title">Total Loss</h6>
                <p class="card-text"><strong id="total-loss">-</strong> dB</p>
                <p class="card-text">"Output" <strong id="power-rx">-</strong> dBm</p>
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
        connections = [],
        lines = [],
        nodeId = 0,
        selectedNode = null;

    function makeDraggable(el) {
        let offsetX = 0,
            offsetY = 0,
            isDragging = false;

        el.addEventListener('mousedown', function(e) {
            isDragging = true;
            offsetX = e.clientX - el.getBoundingClientRect().left;
            offsetY = e.clientY - el.getBoundingClientRect().top;

            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });

        function onMouseMove(e) {
            if (!isDragging) return;
            const map = document.getElementById("map-canvas");
            const rect = map.getBoundingClientRect();
            let x = e.clientX - rect.left - offsetX;
            let y = e.clientY - rect.top - offsetY;

            // Batas agar tidak keluar area
            x = Math.max(0, Math.min(x, map.offsetWidth - el.offsetWidth));
            y = Math.max(0, Math.min(y, map.offsetHeight - el.offsetHeight));

            el.style.left = `${x}px`;
            el.style.top = `${y}px`;

            lines.forEach(line => line.position());
        }

        function onMouseUp() {
            isDragging = false;
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }
    }

    function addNode(type) {
        const el = document.createElement("div");
        el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
        el.style.top = "50px";
        el.style.left = "50px";
        el.setAttribute("id", "node-" + nodeId);
        el.dataset.type = type;

        let label = type;
        let loss = 0;

        if (type === 'Splitter') {
            const splitterType = prompt("Masukkan tipe Splitter (contoh: 1:2, 1:4, 1:8, 1:16, 1:32, 1:64)");
            label = `Splitter ${splitterType}`;
            el.dataset.splitter = splitterType;

            const splitterLosses = {
                '1:2': 3.5,
                '1:4': 7.2,
                '1:8': 10.5,
                '1:16': 13.8,
                '1:32': 17.1,
                '1:64': 19.6
            };
            loss = splitterLosses[splitterType] || 10.5;
        } else if (type === 'ODP') {
            const odpType = prompt("Masukkan jenis ODP (Mini/Besar)");
            label = `ODP ${odpType}`;
            el.dataset.odp = odpType;
            loss = odpType.toLowerCase() === 'besar' ? 0.5 : 0.2;
        }

        el.innerText = label;
        el.dataset.loss = loss;

        el.addEventListener('click', () => {
            if (!selectedNode) {
                selectedNode = el;
                el.classList.add('border-primary');
            } else if (selectedNode !== el) {
                connectNodeElements(selectedNode, el);
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            } else {
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            }
        });

        makeDraggable(el);
        document.getElementById("map-canvas").appendChild(el);

        nodes.push({
            id: nodeId,
            type: type,
            el: el,
            label: label,
            loss: loss
        });

        nodeId++;
    }

    function connectNodeElements(source, target) {
        const line = new LeaderLine(source, target, {
            color: 'blue',
            size: 2
        });
        lines.push(line);
        connections.push({
            from: parseInt(source.id.replace('node-', '')),
            to: parseInt(target.id.replace('node-', ''))
        });
    }

    function saveTopology() {
        const powerInput = parseFloat(document.getElementById("input-power").value);
        const cableLength = parseFloat(document.getElementById("cable-length").value);
        const cableLoss = parseFloat(document.getElementById("cable-type").value);
        const connectorCount = parseInt(document.getElementById("connectors").value);
        const spliceCount = parseInt(document.getElementById("splicing").value);

        let totalLoss = 0;
        let jalur = [];

        for (const node of nodes) {
            const loss = parseFloat(node.el.dataset.loss || 0);
            if (node.type !== 'OLT') jalur.push(node.label);
            totalLoss += loss;
        }

        totalLoss += cableLoss * (cableLength / 1000);
        totalLoss += connectorCount * 0.02;
        totalLoss += spliceCount * 0.01;

        const powerRx = powerInput - totalLoss;

        document.getElementById("total-loss").innerText = totalLoss.toFixed(2);
        document.getElementById("power-rx").innerText = powerRx.toFixed(2);
        document.getElementById("jalur-text").innerText = 'OLT → ' + jalur.join(" → ");
        document.getElementById("info-card").classList.remove("d-none");
    }

    function resetMap() {
        document.getElementById("map-canvas").innerHTML = "";
        document.getElementById("info-card").classList.add("d-none");
        nodes = [];
        connections = [];
        lines.forEach(line => line.remove());
        lines = [];
        selectedNode = null;
        nodeId = 0;
    }

    function exportTopology() {
        const data = {
            nodes: nodes.map(n => ({
                id: n.id,
                type: n.type,
                label: n.label,
                loss: n.loss,
                position: {
                    top: n.el.style.top,
                    left: n.el.style.left
                }
            })),
            connections: connections
        };
        const json = JSON.stringify(data, null, 2);
        const blob = new Blob([json], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'topologi-ftth.json';
        a.click();
        URL.revokeObjectURL(url);
    }

    function importTopology() {
        document.getElementById('json-file').click();
    }

    function loadJsonFile(event) {
        const file = event.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const data = JSON.parse(e.target.result);
            resetMap();

            data.nodes.forEach(n => {
                const el = document.createElement("div");
                el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
                el.style.top = n.position.top;
                el.style.left = n.position.left;
                el.setAttribute("id", "node-" + n.id);
                el.dataset.type = n.type;
                el.dataset.loss = n.loss;
                el.innerText = n.label;

                el.addEventListener('click', () => {
                    if (!selectedNode) {
                        selectedNode = el;
                        el.classList.add('border-primary');
                    } else if (selectedNode !== el) {
                        connectNodeElements(selectedNode, el);
                        selectedNode.classList.remove('border-primary');
                        selectedNode = null;
                    } else {
                        selectedNode.classList.remove('border-primary');
                        selectedNode = null;
                    }
                });

                makeDraggable(el);
                document.getElementById("map-canvas").appendChild(el);
                nodes.push({
                    id: n.id,
                    type: n.type,
                    el: el,
                    label: n.label,
                    loss: n.loss
                });
                nodeId = Math.max(nodeId, n.id + 1);
            });

            setTimeout(() => {
                data.connections.forEach(conn => {
                    const fromEl = document.getElementById("node-" + conn.from);
                    const toEl = document.getElementById("node-" + conn.to);
                    if (fromEl && toEl) {
                        connectNodeElements(fromEl, toEl);
                    }
                });
            }, 200);
        };
        reader.readAsText(file);
    }
</script>
@endpush