export let screenWidth = null;
export let fullScreenEffectsDiv = null;
export let breakpoints = [];

export function inititializeGlobalVariables() {

    breakpoints = [
        { title: "phone", width: 0 },
        { title: "small-tablet", width: 576 },
        { title: "tablet", width: 768 },
        { title: "small-laptop", width: 992 },
        { title: "desktop", width: 1200 },
        { title: "large-screen", width: 1400 }
    ];

    screenWidth = window.innerWidth;
    fullScreenEffectsDiv = document.getElementById("fullScreenEffectDiv");

    window.addEventListener("resize", () => {
        screenWidth = window.innerWidth;

        const event = new CustomEvent("screenWidthChanged", { detail: screenWidth }); // dispatch custom event
        window.dispatchEvent(event);
    });
}

export function getBreakpointWidth(name) {
    const bp = breakpoints.find(b => b.title === name);
    return bp ? bp.width : null;
}
// console.log(getBreakpointWidth("tablet")); // "768"

//FullScreenEffects

export function applyFullScreenEffect(effect, onOff) {
    switch (effect) {
        case "opaqueWhite":
            fullScreenEffectsDiv.classList.toggle("opaqueWhite", onOff);

            break;

        default:
            break;
    }
}
