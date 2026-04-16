//Helpers
import { inititializeGlobalVariables, screenWidth, getBreakpointWidth } from './helpers.js';

//Shop
import { initializeProductGallery } from './shop/productGallery.js';

//Menu filter
import { initializeMenuFilter } from './shop/menuFilter.js';

//Burger menu
import { initializeTopBar, initializeBurgerMenu } from './shop/burgerMenu.js';

//Admin
import { initializeDragAndDropMenu } from './admin/menu/dragNDropMenu.js';

//Forms
import { inititializeMenuProductForm } from './admin/menu/menuProductForm.js';
import { inititializeMenuCategoryForm } from './admin/menu/menuCategoryForm.js';
import { inititializeMenuPageForm } from './admin/menu/menuPageForm.js';

//Reservations
import { initializeReservationIndex } from './admin/reservation/adminReservationIndex.js';
import { initializeAdminReservationForm } from './admin/reservation/adminReservationForm.js';


document.addEventListener('turbo:load', () => {

    const main = document.querySelector("main");
    const pageType = main.dataset.pageType;
    navbar = document.getElementById("navbar");

    inititializeGlobalVariables();
    // addScrollEventListener();

    switch (pageType) {
        //Landing page
        case "shopLandingPage":
            console.log("Preparing landing page");
            initializeTopBar();
            initializeBurgerMenu();
            initializeProductGallery(false);

            break;
        //Client Menu page
        case "shopMenuPage":
            console.log("Preparing menu page");
            initializeTopBar();
            initializeBurgerMenu();
            initializeMenuFilter();
            initializeProductGallery(true);

            break;
        //Dashboard Landing page
        case "adminLandingPage":
            console.log("Preparing menu page");

            break;
        //Dashboard Menu page
        case "adminMenuPage":
            console.log("Preparing menu page");
            initializeDragAndDropMenu();

            break;
        //Product forms
        case "newProductForm":
            console.log("Preparing new product ");

            inititializeMenuProductForm("new");

            break;
        case "editProductForm":
            console.log("Preparing edit product ");

            inititializeMenuProductForm("edit");

            break;
        //Category forms
        case "newCategoryForm":
            console.log("Preparing new category ");

            inititializeMenuCategoryForm("new");

            break;
        case "editCategoryForm":
            console.log("Preparing edit category ");

            inititializeMenuCategoryForm("edit");

            break;
        //Page forms
        case "newPageForm":
            console.log("Preparing new page ");

            inititializeMenuPageForm("new");

            break;
        case "editPageForm":
            console.log("Preparing edit page ");

            inititializeMenuPageForm("edit");

            break;
        //Dashboard reservation page
        case "adminReservationPage":
            console.log("Preparing edit page ");

            initializeReservationIndex();

            break;
        default:
            break;
    }
});

//#region OnScroll effects
// function addScrollEventListener() {
//     window.addEventListener("scroll", function () {
//         if (navbar) {
//             if (screenWidth <= getBreakpointWidth('tablet')) {
//                 if (window.scrollY > 100) { // Change number for when effect triggers
//                     navbar.classList.add("scrolled");
//                 } else {
//                     navbar.classList.remove("scrolled");
//                 }
//             }
//             else {
//                 navbar.classList.remove("scrolled");
//             }
//         }
//     });
// }
//#endregion





