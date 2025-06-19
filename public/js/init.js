import { resetState } from './topologyState.js';
import { addNode } from './nodeUtils.js';
import { selectCable } from './cableUtils.js';
import { saveTopology, loadTopology } from './saveLoad.js';

window.onload = () => {
    const labId = document.getElementById('lab-id')?.value;
    resetState();
    if (labId) loadTopology(labId);
};

// Fungsi agar bisa dipakai dari HTML onclick
window.addNode = addNode;
window.selectCable = selectCable;
window.saveTopology = saveTopology;
window.confirmSplitter = () => {
    const type = document.getElementById('splitterTypeSelect').value;
    addNode(`Splitter ${type}`);
};
