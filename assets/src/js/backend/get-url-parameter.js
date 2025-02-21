export function zwssgrGetUrlParameter(zwssgrName) {
    const zwssgrUrlParams = new URLSearchParams(window.location.search);
    return zwssgrUrlParams.get(zwssgrName);
}