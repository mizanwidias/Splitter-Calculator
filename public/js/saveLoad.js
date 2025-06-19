import { topologyState } from './topologyState.js';

export function saveTopology() {
    const topology = {
        nodes: [],
        connections: []
    };

    const nodeElements = Array.from(document.querySelectorAll("#map-canvas > div"));
    nodeElements.forEach(node => {
        topology.nodes.push({
            id: node.id,
            type: node.querySelector('strong')?.innerText || '',
            loss: parseFloat(node.dataset.loss || 0),
            power: parseFloat(node.dataset.power || 0),
            top: node.style.top,
            left: node.style.left,
        });
    });

    topologyState.lines.forEach(conn => {
        topology.connections.push({
            from: conn.from,
            to: conn.to,
            cable: conn.cable
        });
    });

    submitTopology(topology);
}

export async function submitTopology(topology) {
    const power = parseFloat(document.getElementById('input-power').value || 0);
    const labId = document.getElementById('lab-id').value;

    try {
        const response = await fetch(`/topologi/save/${labId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ ...topology, power })
        });

        const data = await response.json();
        if (data.success) {
            Swal.fire('Berhasil', data.message || 'Topologi berhasil disimpan!', 'success');
        } else {
            Swal.fire('Gagal', (data.errors || ['Gagal menyimpan topologi']).join('<br>'), 'error');
        }
    } catch (error) {
        console.error(error);
        Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
    }
}

export function loadTopology(id) {
    fetch(`/topologi/load/${id}`)
        .then(res => res.json())
        .then(data => {
            if (!data?.nodes) return;
            // TODO: render ulang dari DB
            console.log('Loaded nodes:', data.nodes);
        });
}
