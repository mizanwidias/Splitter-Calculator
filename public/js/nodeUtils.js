import { topologyState } from './topologyState.js';
import { makeDraggable } from './dragUtils.js';
import { connectNodeElements } from './connectionUtils.js';

export function addNode(type) {
    if (type === 'Splitter') {
        new bootstrap.Modal(document.getElementById('splitterModal')).show();
        return;
    }

    const el = document.createElement("div");
    el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
    el.style.top = "100px";
    el.style.left = "100px";
    el.setAttribute("id", `node-${topologyState.nodeId}`);

    let label = type;
    let loss = 0;

    if (type.startsWith('Splitter')) {
        const splitLoss = {
            '1:2': 3.5, '1:4': 7.2, '1:8': 10.5,
            '1:16': 13.5, '1:32': 17.0, '1:64': 20.5,
        };
        const ratio = type.split(' ')[1];
        loss = splitLoss[ratio] || 0;
    }

    if (type === 'ODP') {
        const odpType = prompt("Masukkan jenis ODP (Mini/Besar)");
        label = `ODP ${odpType}`;
        el.dataset.odp = odpType;
        loss = odpType.toLowerCase() === 'besar' ? 0.5 : 0.2;
    }
    let icon = '';
    if (type === 'OLT') icon = '<i class="fas fa-broadcast-tower fa-lg d-block mb-1"></i>';
    if (type === 'Splitter' || type.startsWith('Splitter')) icon = '<i class="fas fa-code-branch fa-lg d-block mb-1"></i>';
    if (type === 'ODP') icon = '<i class="fas fa-network-wired fa-lg d-block mb-1"></i>';
    if (type === 'Client') icon = '<i class="fas fa-user fa-lg d-block mb-1"></i>';
    
    el.innerHTML = `
        ${icon}
        <strong>${label}</strong>
        <div class="output-power" style="font-size: 12px; color: green;"></div>
    `;
    
    el.dataset.loss = loss;
    el.dataset.power = "";

    el.addEventListener('click', () => {
        if (!topologyState.selectedNode) {
            topologyState.selectedNode = el;
            el.classList.add('border-primary');
        } else if (topologyState.selectedNode !== el) {
            const length = parseFloat(prompt("Panjang kabel (m):", document.getElementById("cable-length").value));
            if (!isNaN(length)) {
                connectNodeElements(topologyState.selectedNode, el, length);
            }
            topologyState.selectedNode.classList.remove('border-primary');
            topologyState.selectedNode = null;
        } else {
            topologyState.selectedNode.classList.remove('border-primary');
            topologyState.selectedNode = null;
        }
    });

    makeDraggable(el);
    document.getElementById("map-canvas").appendChild(el);
    topologyState.nodeId++;

    topologyState.actions.push({ type: "add-node", node: el });
}
