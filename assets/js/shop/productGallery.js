import { screenWidth, getBreakpointWidth, applyFullScreenEffect, fullScreenEffectsDiv } from '../helpers.js';

let currentFocusId = 0;
let switchFocusCd = 200;
let switchFocusCount = 0;

let productFocus = [];
export function initializeProductGallery(isMenu) {

    productFocus = document.querySelector("#productGalleryFocus");
    const closeBttn = productFocus.querySelector("#focusCloseBttn");
    const productsWithPhotos = document.querySelectorAll(".photoPreview");
    if (!productsWithPhotos.length) return;

    if (screenWidth >= getBreakpointWidth('small-laptop') && !isMenu) {
        productFocus.classList.toggle("hidden", false);
    }

    productsWithPhotos.forEach((product) => {

        let title = product.dataset.title;
        let description = product.dataset.description;
        let photo = product.dataset.photoFilename;

        product.addEventListener('pointerdown', () => {

            focusOnProduct(title, description, photo);

        });

    });

    switchFocusData(productsWithPhotos[0].dataset.title
        , productsWithPhotos[0].dataset.description
        , productsWithPhotos[0].dataset.photoFilename);

    closeBttn.addEventListener("pointerdown", () => {
        mobileFocusModeOff();
    })

    window.addEventListener("screenWidthChanged", (e) => {
        if (e.detail >= getBreakpointWidth('small-laptop')) {
            if (!isMenu) productFocus.classList.toggle("hidden", false);
        }
        else {
            productFocus.classList.toggle("hidden", true);
            mobileFocusModeOff();

        }
    });
}

function focusOnProduct(title, description, photo, onLoad = false) {
    switchFocusData(title, description, photo);

    if (screenWidth < getBreakpointWidth('small-laptop')) {
        mobileFocusModeOn();
    }
}

function mobileFocusModeOn() {
    applyFullScreenEffect("opaqueWhite", true);
    productFocus.classList.toggle("hidden", false);

    fullScreenEffectsDiv.addEventListener("pointerdown", () => {
        mobileFocusModeOff();
    }, { once: true })

}

function mobileFocusModeOff() {
    applyFullScreenEffect("opaqueWhite", false);
    productFocus.classList.toggle("hidden", true);
    // console.log("Exit focus mode");
}

function switchFocusData(title, description, photo) {

    // console.log("Focus");
    productFocus.querySelector("#focusImg").style.backgroundImage = `url('/media/photos/produits/${photo}')`;;
    productFocus.querySelector("#focusTitle").textContent = title;
    productFocus.querySelector("#focusDescription").textContent = description;
}