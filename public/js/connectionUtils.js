import { topologyState } from './topologyState.js';

export function connectNodeElements(source, target, length) {
    const lossCable = length * topologyState.selectedCableLoss;
    const lossTarget = parseFloat(target.dataset.loss || 0);
    const connectorLoss = (parseInt(document.getElementById("connectors").value) || 0) * 0.2;
    const splicingLoss = (parseInt(document.getElementById("splicing").value) || 0) * 0.1;
    const totalLoss = lossCable + lossTarget + connectorLoss + splicingLoss;

    const sourcePower = parseFloat(source.dataset.power || document.getElementById("input-power").value || 0);
    const powerRx = sourcePower - totalLoss;
    target.dataset.power = powerRx.toFixed(2);
    target.querySelector(".output-power").innerText = `${powerRx.toFixed(2)} dB`;

    const line = new LeaderLine(
        LeaderLine.pointAnchor(source, { x: '50%', y: '50%' }),
        LeaderLine.pointAnchor(target, { x: '50%', y: '50%' }),
        {
            color: topologyState.selectedCableColor,
            size: 2,
            path: 'straight',
            startPlug: 'none',
            endPlug: 'none',
            dash: { animation: true },
            middleLabel: LeaderLine.pathLabel(`-${lossCable.toFixed(2)} dB`, {
                color: 'red',
                fontSize: '12px'
            }),
            startLabel: topologyState.selectedCableName
        }
    );

    topologyState.lines.push({ from: source.id, to: target.id, cable: topologyState.selectedCableName, line });

    topologyState.actions.push({
        type: 'add-connection',
        line,
        from: source.id,
        to: target.id
    });
}
