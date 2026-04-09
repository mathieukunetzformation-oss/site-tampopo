
import { screenWidth, getBreakpointWidth, applyFullScreenEffect, fullScreenEffectsDiv } from './helpers.js';

//#region Burger Menu
let burgerMenu;
let burgerMenuBttn;
let burgerMenuBttnIcon;
let burgerMenuRows;
let burgerMenuIsCollapsed;

let topBar;

export function initializeTopBar() {
    topBar = document.getElementById("topNavBar");
    topBar.classList.toggle("hidden", screenWidth < getBreakpointWidth('small-laptop'));
    window.addEventListener('screenWidthChanged', (e) => {
        topBar.classList.toggle("hidden", e.detail < getBreakpointWidth('small-laptop'));

    })
}

export function initializeBurgerMenu() {

    burgerMenu = document.getElementById("burgerMenu");
    burgerMenuBttn = document.querySelector(".shopHeader__navbar__burger__bttn");
    burgerMenuBttnIcon = document.getElementById("burgerIcon");
    burgerMenuRows = document.querySelectorAll(".shopHeader__navbar__burger__link");
    burgerMenuIsCollapsed = true;

    burgerMenuBttn.addEventListener("pointerdown", () => {
        toggleBurgerMenuRows();
    })

    burgerMenu.classList.toggle("hidden", screenWidth >= getBreakpointWidth('small-laptop'));
    window.addEventListener('screenWidthChanged', (e) => {
        burgerMenu.classList.toggle("hidden", e.detail >= getBreakpointWidth('small-laptop'));
        console.log("here" + (e.detail >= getBreakpointWidth('small-laptop')))
    })
}

function toggleBurgerMenuRows() {
    burgerMenuIsCollapsed = !burgerMenuIsCollapsed;
    burgerMenuRows.forEach(element => {
        element.classList.toggle("collapsed", burgerMenuIsCollapsed);
    });
    burgerMenuBttnIcon.classList = burgerMenuIsCollapsed ? "fa-solid fa-bars" : "fa-solid fa-xmark";

    if (!burgerMenuIsCollapsed) {
        toggleClickEvents(true);
    }
    else {
        toggleClickEvents(false);
    }

    applyFullScreenEffect("opaqueWhite", !burgerMenuIsCollapsed);
}

function onClick() { //need this function to remove listeners
    burgerMenuIsCollapsed = true;
    applyFullScreenEffect("opaqueWhite", false);
    burgerMenuRows.forEach(element => {
        element.classList.toggle("collapsed", true);
    });
    burgerMenuBttnIcon.classList = "fa-solid fa-bars";

}

function toggleClickEvents(bool) {
    if (bool) {
        burgerMenuRows.forEach(element => {
            element.addEventListener("click", onClick, { once: true })
        });
        fullScreenEffectsDiv.addEventListener("pointerdown", onClick, { once: true })
    } else {
        burgerMenuRows.forEach(element => {
            element.removeEventListener("click", onClick)
        });
        fullScreenEffectsDiv.removeEventListener("pointerdown", onClick)
    }

}

//#endregion