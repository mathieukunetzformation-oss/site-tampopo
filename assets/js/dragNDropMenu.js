//#region DropZones variables
/**
 * All drop zones in the menu
 */
let allDropZones = [];
/**
 * All page drop zones in the menu.
 */
let pageDropZones = [];
/**
 * All category drop zones in the menu. This includes pages' orphan drop zones.
 */
let categoryDropZones = [];
/**
 * All product drop zones in the menu. This includes categories' orphan drop zones.
 */
let productDropZones = [];

/**
 * Potential drop zones for the current dragged element
 */
let candidateDropZones = [];

/**
 * Currently dragged element
 */
let draggedItem = null;

/**
 * Current clone element to follow pointer
 */
let dragClone = null;

/**
 * Max distance to a drop zone for it to be registered as targeted by the pointer when dropping.
 */
let dropZoneDetectionThreshold;

/**
 * All drag handles elements.
 */
let dragHandles = [];

//#endregion

//#region Scroll wile dragging variables
/**
 * // Pixels per frame when auto-scrolling
 */
let navbar = null;

let autoScrollInterval = null;
/**
 * // Pixels per frame when auto-scrolling
 */
let scrollSpeed;
/**
 * Pixels from top/bottom to start auto-scrolling
 */
let scrollThreshold;
/**
 * A 'request animation frame' to not have the move function fire a lot more than needed
 */
let raf = null; //

//#endregion

/**
 * Main function to initialize the menu
 */
export function initializeDragAndDropMenu() {

    dropZoneDetectionThreshold = 30;
    scrollSpeed = 15;
    scrollThreshold = 80;

    cacheDropZones();
    cacheDragHandles();
    cacheSaveButton();
    addCollapseEventListener();
    if (dragHandles) addDragHandlesEventListener();
}

//#region Drag and Drop
/**
 * Cache all drop zones at init
 */
function cacheDropZones() {

    allDropZones = document.querySelectorAll('.dropZone');

    productDropZones = [
        ...document.querySelectorAll('.adminMenu__page__element__category__element__product__dropZone'),
        ...document.querySelectorAll('.adminMenu__page__element__category__element__empty__orphanDropZone')
    ];

    categoryDropZones = [
        ...document.querySelectorAll('.adminMenu__page__element__category__dropZone'),
        ...document.querySelectorAll('.adminMenu__page__element__empty__orphanDropZone')
    ];

    pageDropZones = [
        ...document.querySelectorAll('.adminMenu__page__dropZone'),
        ...document.querySelectorAll('.adminMenu__empty__orphanDropZone')
    ];
}
/**
 * Cache all drag handles at init
 */
function cacheDragHandles() {

    dragHandles = document.querySelectorAll('.dragHandle');

}

/**
 * Add listeners to dran hangles at init
 */
function addDragHandlesEventListener() {

    dragHandles.forEach(handle => {

        const item = handle.closest('.draggable');

        handle.addEventListener('pointerdown', (e) => {
            e.preventDefault();

            handle.setPointerCapture(e.pointerId); // necessary to handle cursor going outsidde of element
            draggedItem = item;
            item.classList.add('dragging');

            if (!item.classList.contains('collapsed')) {
                console.log("Collapsing before dragging");
                item.classList.toggle('collapsed', true)
            }

            // Create clone
            createCloneElement(e, item);
            cacheCandidateDropZones();


            document.addEventListener('pointerup', onMouseUp);
            document.addEventListener('pointermove', onMouseMove);
        });

    });
}

/**
 * Called when mouse is moving while dragging item
 * @param {PointerEvent} e - The pointer event triggering the drag
 */
function onMouseMove(e) {
    if (raf) return;

    raf = requestAnimationFrame(() => {
        raf = null;

        moveClone(e);

        const { closestDropZone, minDistance } = getClosestDropZone(e);
        candidateDropZones.forEach(element => {
            element.classList.remove('hover');
        });

        if (closestDropZone && minDistance < dropZoneDetectionThreshold) {
            closestDropZone.classList.add('hover');
        }

    });

    //Check if we scroll
    handleAutoScroll(e);
}

/**
 * Called when click stop while dragging
 * @param {PointerEvent} e - The pointer event triggering the drag
 */
function onMouseUp(e) {

    if (draggedItem) { //there is dragged item

        const { closestDropZone, minDistance } = getClosestDropZone(e); //get the dropzone closest to the mouse

        if (closestDropZone && minDistance < dropZoneDetectionThreshold) {

            const initialWrapper = getElementWrapper(draggedItem);
            const initialParentEntity = getParentEntity(initialWrapper);

            const targetWrapper = getElementWrapper(closestDropZone);
            const targetParentEntity = getParentEntity(targetWrapper);

            console.log('Dropping into:', targetWrapper);

            const isOrphanDZ = closestDropZone.classList.contains('orphanDZ');

            //Move dragged element
            if (isOrphanDZ) { //if the drop zone an orphanDropzone
                const element = getElementNode(targetWrapper);
                element.insertBefore(initialWrapper, element.children[1]);

                //hide empty msg div
                const emptyMsg = closestDropZone.closest('.emptyMsgDiv');
                console.log("drop zone :" + closestDropZone.classList);
                console.log("hide :" + emptyMsg.classList);
                console.log("hide :" + emptyMsg.classList);
                console.log("hide :" + emptyMsg.classList);
                emptyMsg.classList.toggle("hidden", true);
            }
            else {

                if (closestDropZone.classList.contains("top")) {
                    targetWrapper.before(initialWrapper);
                } else {
                    targetWrapper.after(initialWrapper);
                }

            }

            //Display empty msg div and orphan dropzone if initial wrapper is now empty
            const initialParentChildEntities = getChildEntities(initialParentEntity);
            const targetParentChildEntities = getChildEntities(targetParentEntity);

            if (initialParentChildEntities.length === 0) {

                const emptyMsg = initialParentEntity.querySelector('.emptyMsgDiv');
                console.log(emptyMsg.classList);
                console.log("show :" + emptyMsg.classList);

                emptyMsg.classList.toggle("hidden", false);
            }

            //update class accordingly
            //mark initial and target parent (category) and moved elem for changes when applying changes

            initialParentEntity.classList.add("markedForChange");

            if (isOrphanDZ) targetWrapper.classList.add("markedForChange");
            else targetParentEntity.classList.add("markedForChange");

            //in the case where an element is just reordered within its parent: both arrays are the same but we still run it 

            displaySaveButton();
        }
    }

    // remove highlighting classes 
    allDropZones.forEach(item => {

        item.classList.toggle('hover', false);
        item.classList.toggle('candidate', false);

    });
    draggedItem.classList.remove('dragging');
    draggedItem = null;

    // remove clone element
    if (dragClone) {
        dragClone.remove();
        dragClone = null;
    }

    //clear scroll interval
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval);
        autoScrollInterval = null;
    }

    //remove move and up listeners
    document.removeEventListener('pointermove', onMouseMove);
    document.removeEventListener('pointerup', onMouseUp);

}

//Clone functions
/**
 * Create a clone of the currently dragged element for visuals
 * @param {PointerEvent} e - The pointer event triggering the drag
 * @param {HTMLElement} item - The element being dragged 
 */
function createCloneElement(e, item) {
    // Create clone
    dragClone = item.cloneNode(true);
    dragClone.classList.add('dragClone');
    dragClone.classList.remove('dragging');

    document.body.appendChild(dragClone);
    dragClone.style.position = "fixed";
    dragClone.style.pointerEvents = "none";
    dragClone.style.zIndex = "9999";
    dragClone.style.left = e.clientX + "px";
    dragClone.style.top = e.clientY + "px";

    const rect = item.getBoundingClientRect();
    dragClone.style.width = rect.width + "px"; //so it doesn't get impacted by flex
    dragClone.style.height = rect.height + "px";
}

/**
 * Move the clone to the mouse position
 * @param {PointerEvent} e - The pointer event triggering the drag
 */
function moveClone(e) {
    if (dragClone) {
        dragClone.style.left = e.clientX + "px";
        dragClone.style.top = e.clientY + "px";
    }
}

/**
 * Cache the candidate dropzone for the current dragged item
 */
function cacheCandidateDropZones() {

    const draggedItemClass = draggedItem.classList.contains('adminMenu__page__element__category__element__product__element') ? "product" :
        draggedItem.classList.contains('adminMenu__page__element__category__element') ? "category" :
            draggedItem.classList.contains('adminMenu__page__element') ? "page" :
                [];

    const allPotentialDropZones =
        draggedItemClass === 'product' ? productDropZones :
            draggedItemClass === 'category' ? categoryDropZones :
                draggedItemClass === 'page' ? pageDropZones :
                    [];

    const datasetKey =
        draggedItemClass === 'product' ? 'productId' :
            draggedItemClass === 'category' ? 'categoryId' :
                draggedItemClass === 'page' ? 'pageId' :
                    null;

    const draggedId = datasetKey ? draggedItem.dataset[datasetKey] : null;

    const elemWrapper = getElementWrapper(draggedItem);
    const prevWrapper = elemWrapper.previousElementSibling;
    const nextWrapper = elemWrapper.nextElementSibling;

    const prevId = datasetKey ? prevWrapper?.querySelector(`[data-${datasetKey.replace('Id', '-id')}]`)?.dataset[datasetKey] : null;
    const nextId = datasetKey ? nextWrapper?.querySelector(`[data-${datasetKey.replace('Id', '-id')}]`)?.dataset[datasetKey] : null;


    candidateDropZones = [...allPotentialDropZones].filter(el => {

        const zoneId = datasetKey ? el.dataset[datasetKey] : null;

        if (zoneId === draggedId) return false; //drop zone of the element
        if (zoneId === prevId && el.classList.contains('bot')) return false; // bottom zone of previous element
        if (zoneId === nextId && el.classList.contains('top')) return false; // top zone of next element

        return true;
    });

    candidateDropZones.forEach(element => {

        element.classList.toggle("candidate", true);

    });
}

/**
 * Return the candidate dropzone closest to the mouse
 * @param {PointerEvent} e - The pointer event triggering the drag
 * @returns 
 */
function getClosestDropZone(e) {

    let closestDropZone = null;
    let minDistance = Infinity;

    for (const element of candidateDropZones) {

        const rect = element.getBoundingClientRect();
        const y = rect.top + rect.height / 2;

        const distance = Math.abs(e.clientY - y);

        if (distance < minDistance) {
            minDistance = distance;
            closestDropZone = element;
        }
    }

    return { closestDropZone, minDistance };
}


/**
 * Return the wrapper node of a given draggable element, dropzone or orphandropzone
 * @param {HTMLElement} childElem 
 * @returns 
 */

function getElementWrapper(childElem) {
    if (
        childElem.classList.contains('adminMenu__page__element__category__element__product__element') ||
        childElem.classList.contains('adminMenu__page__element__category__element__product__dropZone')) {

        return childElem.closest('.adminMenu__page__element__category__element__product');
    }


    if (childElem.classList.contains('adminMenu__page__element__category__element__empty__orphanDropZone') ||
        childElem.classList.contains('adminMenu__page__element__category__element') ||
        childElem.classList.contains('adminMenu__page__element__category__dropZone')) {

        return childElem.closest('.adminMenu__page__element__category');
    }

    if (childElem.classList.contains('adminMenu__page__element__empty__orphanDropZone') ||
        childElem.classList.contains('adminMenu__page__element') ||
        childElem.classList.contains('adminMenu__page__dropZone')) {

        return childElem.closest('.adminMenu__page');
    }



    return null;
}

/**
 * Return the chield "__element" node entity of the inputed wrapper 
 * @param {HTMLElement} child 
 * @returns 
 */
function getElementNode(wrapper) {

    if (wrapper.classList.contains("adminMenu__page")) return wrapper.querySelector(".adminMenu__page__element");
    if (wrapper.classList.contains("adminMenu__page__element__category")) return wrapper.querySelector(".adminMenu__page__element__category__element");
    if (wrapper.classList.contains("adminMenu__page__element__category__element__product")) return wrapper.querySelector(".adminMenu__page__element__category__element__product__element");

}


/**
 * Return the corresponding children node entities the inputed entities (Return all products of categories, all categories of a page, all pages of the menu)
 * @param {HTMLElement} parent 
 * @returns 
 */
function getChildEntities(parent) {
    const childEntities =
        parent.classList.contains('adminMenu__page__element__category') ? parent.querySelectorAll('.adminMenu__page__element__category__element__product') :
            parent.classList.contains('adminMenu__page') ? parent.querySelectorAll('.adminMenu__page__element__category') :
                parent.classList.contains('adminMenu') ? parent.querySelectorAll('.adminMenu__page') :
                    null;

    return childEntities;
}

/**
 * Return the node parent entity of the inputed node entity (Return the category of a product, the page of a category)
 * @param {HTMLElement} child 
 * @returns 
 */
function getParentEntity(child) {
    const parent =
        child.classList.contains('adminMenu__page__element__category__element__product') ? child.closest('.adminMenu__page__element__category') :
            child.classList.contains('adminMenu__page__element__category') ? child.closest('.adminMenu__page') :
                child.classList.contains('adminMenu__page') ? child.closest('.adminMenu') :
                    [];

    return parent;
}

/**
 * Return the node parent entity of the inputed node entity (Return the category of a product, the page of a category)
 * @param {PointerEvent} e  -
 * @returns 
 */
function handleAutoScroll(e) {
    const viewportHeight = window.innerHeight;

    // Clear any previous scroll interval
    if (autoScrollInterval) {
        clearInterval(autoScrollInterval);
        autoScrollInterval = null;
    }

    if (e.clientY < scrollThreshold) { //mouse/finger is close to the top -> scroll up
        autoScrollInterval = setInterval(() => {
            window.scrollBy({ top: -scrollSpeed, behavior: 'auto' });
        }, 16);
    } else if (e.clientY > viewportHeight - scrollThreshold) {//mouse/finger is close to the bottom -> scroll down
        autoScrollInterval = setInterval(() => {
            window.scrollBy({ top: scrollSpeed, behavior: 'auto' });
        }, 16);
    }
}
//#endregion

//#region Collapsable elements

function addCollapseEventListener() {
    const allCollapsableElement = [...document.querySelectorAll('.adminMenu__page__element__category__element'),
    ...document.querySelectorAll('.adminMenu__page__element')];

    if (allCollapsableElement) {
        allCollapsableElement.forEach(element => {
            //get the dropdown icon
            const collapseBttn = element.querySelector('.collapseBttn');

            collapseBttn.addEventListener('pointerdown', (e) => {
                e.preventDefault();

                element.classList.toggle('collapsed');
            });
        });
    }
}
//#endregion

//#region Save Changes
let saveButton = []; document.querySelector(".saveBttn");

function cacheSaveButton() {
    saveButton = document.querySelector(".saveBttn");

    if (saveButton) {

        console.log("Adding saving event to button");

        saveButton.addEventListener("pointerdown", (e) => {
            saveMenuStructure();
        })
    }
    else {
        console.error("Could not find save button in page.")
    }
}

function saveMenuStructure() {

    console.log("Saving")
    const menuElem = document.querySelector(".adminMenu");
    const pages = [];
    const categories = [];
    const products = [];


    const pageOrderChanged = menuElem.classList.contains("markedForChange");

    document.querySelectorAll('.adminMenu__page').forEach((pageWrapper, pageIndex) => {

        const pageElem = pageWrapper.querySelector('.adminMenu__page__element'); //find the page element
        if (!pageElem) return;

        const pageId = pageElem.dataset.pageId; //extract the pageID

        if (pageOrderChanged) { //if order of pages has changed
            pages.push({
                id: pageId,
                position: (pageIndex + 1) * 10,
            });
        }

        const categoryOrderHasChanged = pageWrapper.classList.contains("markedForChange");

        pageWrapper.querySelectorAll('.adminMenu__page__element__category').forEach((catWrapper, catIndex) => {

            const catElem = catWrapper.querySelector('.adminMenu__page__element__category__element');
            if (!catElem) return;

            const categoryId = catElem.dataset.categoryId;

            if (categoryOrderHasChanged) {

                categories.push({
                    id: categoryId,
                    position: (catIndex + 1) * 10,
                    page: pageId,
                });
            }

            const productOrderHasChanged = catWrapper.classList.contains("markedForChange");

            if (productOrderHasChanged) {
                catWrapper.querySelectorAll('.adminMenu__page__element__category__element__product').forEach((prodWrapper, prodIndex) => {

                    const prodElem = prodWrapper.querySelector('.adminMenu__page__element__category__element__product__element');

                    if (!prodElem) return;
                    console.log("Product new index = " + (prodIndex + 1));
                    products.push({
                        id: prodElem.dataset.productId,
                        position: (prodIndex + 1) * 10,
                        category: categoryId,
                    });
                });
            }
        });
    });

    const data = {
        pages,
        categories,
        products
    };

    console.log(data);
    sendMenuToServer(data);

}

function sendMenuToServer(data) {

    fetch('/admin/menu/reorder', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(response => {
            console.log("Saved", response);
            hideSaveButton();
        })
        .catch(err => {
            console.error("Error saving menu", err);
        });

}

function hideSaveButton() {
    document.querySelector(".saveBttn").classList.toggle("hidden", true);

}

function displaySaveButton() {
    document.querySelector(".saveBttn").classList.toggle("hidden", false);

}
//#endregion