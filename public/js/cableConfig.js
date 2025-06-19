// State terpusat dalam object
const cableState = {
    lossPerKM: 0.2 / 1000,
    color: 'black',
    name: 'Dropcore'
};

export function setCableConfig({ lossPerKM, color, name }) {
    cableState.lossPerKM = lossPerKM;
    cableState.color = color;
    cableState.name = name;
}

export function getCableConfig() {
    return cableState;
}
