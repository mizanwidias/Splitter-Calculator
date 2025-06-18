@extends('layouts.app')

@section('title', 'Topologi Interaktif')

@section('content')
<div class="row">
    <div class="col-md-3">
        <h5>Tambah Node</h5>
        @foreach (['OLT', 'Splitter', 'ODP', 'Client'] as $type)
            <button class="btn btn-sm btn-outline-primary mb-1" onclick="addNode('{{ $type }}')">+ {{ $type }}</button>
        @endforeach

        <hr>
        <h5>Jenis Kabel</h5>
        <select id="cableType" class="form-select form-select-sm">
            <option value="Patchcord">Patchcord</option>
            <option value="Dropcore">Dropcore</option>
            <option value="ADSS">ADSS</option>
        </select>

        <hr>
        <button class="btn btn-success btn-sm" onclick="saveTopology()">Simpan</button>
        <button class="btn btn-danger btn-sm" onclick="resetMap()">Reset</button>
    </div>

    <div class="col-md-6 position-relative" id="map-canvas" style="min-height: 500px; background: #f4f4f4; border: 1px solid #ccc;">
        <!-- Node akan ditempatkan di sini -->
    </div>

    <div class="col-md-3">
        <div id="info-card" class="card d-none">
            <div class="card-body">
                <h6>Total Loss</h6>
                <p><strong id="total-loss">-</strong> dB</p>
                <p><strong id="power-rx">-</strong> dBm</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leader-line/1.0.7/leader-line.min.js"></script>
<script>
let nodeId = 0;
let nodes = [];
let lines = [];
let selectedNode = null;

function addNode(type) {
    const id = 'node-' + nodeId++;
    const el = document.createElement("div");
    el.className = "node position-absolute text-center";
    el.id = id;
    el.style.top = "100px";
    el.style.left = "100px";
    el.innerHTML = `
        <img src="/assets/icons/${type.toLowerCase()}.png" width="40">
        <div contenteditable="true" class="small mt-1">${type}</div>
    `;
    el.dataset.type = type;
    el.style.cursor = "move";
    el.onclick = () => connectHandler(el);

    document.getElementById("map-canvas").appendChild(el);

    interact(el).draggable({
        onmove: function (event) {
            const target = event.target;
            const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
            const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

            target.style.transform = `translate(${x}px, ${y}px)`;
            target.setAttribute('data-x', x);
            target.setAttribute('data-y', y);

            updateLines();
        }
    });
}

function connectHandler(el) {
    if (!selectedNode) {
        selectedNode = el;
        el.style.border = '2px dashed blue';
    } else if (selectedNode !== el) {
        const cable = document.getElementById('cableType').value;
        const line = new LeaderLine(
            LeaderLine.pointAnchor(selectedNode, { x: '50%', y: '50%' }),
            LeaderLine.pointAnchor(el, { x: '50%', y: '50%' }),
            { color: 'green', size: 2, dash: { animation: true }, startLabel: cable }
        );
        lines.push({ from: selectedNode.id, to: el.id, cable: cable, line: line });
        selectedNode.style.border = '';
        selectedNode = null;
    } else {
        el.style.border = '';
        selectedNode = null;
    }
}

function updateLines() {
    lines.forEach(l => l.line.position());
}

function resetMap() {
    document.getElementById('map-canvas').innerHTML = '';
    document.getElementById('info-card').classList.add('d-none');
    nodeId = 0;
    nodes = [];
    lines.forEach(l => l.line.remove());
    lines = [];
}

function saveTopology() {
    let totalLoss = 0;
    lines.forEach(l => {
        if (l.cable === 'Patchcord') totalLoss += 0.5;
        if (l.cable === 'Dropcore') totalLoss += 0.35;
        if (l.cable === 'ADSS') totalLoss += 0.25;
    });

    document.getElementById('total-loss').innerText = totalLoss.toFixed(2);
    document.getElementById('power-rx').innerText = (-20 - totalLoss).toFixed(2);
    document.getElementById('info-card').classList.remove('d-none');
}
</script>
@endpush
