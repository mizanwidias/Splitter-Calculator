import { topologyState } from './topologyState.js';

export function selectCable(lossPerKM, type, el) {
    topologyState.selectedCableLoss = parseFloat(lossPerKM) / 1000;
    topologyState.selectedCableColor = type === 'patchcord' ? 'yellow' : 'black';
    topologyState.selectedCableName = type.charAt(0).toUpperCase() + type.slice(1);

    document.querySelectorAll('.cable-option').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
}
