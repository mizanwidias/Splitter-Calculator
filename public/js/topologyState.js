export const topologyState = {
    nodeId: 0,
    selectedNode: null,
    selectedCableLoss: 0.2 / 1000,
    selectedCableColor: 'black',
    selectedCableName: 'Dropcore',
    actions: [],
    lines: []
};

export function resetState() {
    topologyState.nodeId = 0;
    topologyState.selectedNode = null;
    topologyState.selectedCableLoss = 0.2 / 1000;
    topologyState.selectedCableColor = 'black';
    topologyState.selectedCableName = 'Dropcore';
    topologyState.actions = [];
    topologyState.lines = [];
}
