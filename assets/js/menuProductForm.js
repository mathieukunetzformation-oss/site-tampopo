export function inititializeMenuProductForm(mode) {

    console.log("Initializing " + mode + " product form");

    const categorySelect = document.querySelector('.category-select');
    const orderSelect = document.querySelector('.order-select');

    if (!categorySelect || !orderSelect) return;

    let productId = null;
    let categoryId = null;

    const main = document.querySelector("main");

    if (mode === "edit") {
        productId = main.dataset.productId;
        categoryId = main.dataset.categoryId;

    }
    else if (mode === "new") {
        categoryId = main.dataset.categoryId;
        console.log("New product form page script. PreFilled category :" + categoryId);

    }

    function fetchCategories() {

        console.log("Fetching pages");


        fetch('/admin/menu/page/fetchAll')
            .then(response => response.json())
            .then(data => {

                // console.log(data);
                categorySelect.innerHTML = '';

                const pages = data.pages;

                pages.forEach(page => {
                    const optionGroup = document.createElement('optgroup');
                    optionGroup.label = page.title;

                    fetch('/admin/menu/category/fetchById/' + page.id)
                        .then(response => response.json())
                        .then(data => {

                            const categories = data.categories;
                            categories.forEach(category => {

                                const option = document.createElement('option');
                                option.label = category.title;
                                option.value = category.id;

                                if (categoryId === String(category.id)) {
                                    console.log("Category selected =" + categoryId);

                                    option.selected = true;
                                    if (mode === "edit") option.label += " (Catégorie actuelle)";

                                }

                                optionGroup.appendChild(option);
                            });


                        })
                        .catch(error => {
                            console.error('Impossible de charger les catégories:', error);
                        });

                    categorySelect.appendChild(optionGroup);
                });

                console.log("Pages loaded");
                console.log("Category dispplyed =" + categoryId)
                let categoryDefaultSelectValue = categoryId;

                fetchProductsByCategory(categoryDefaultSelectValue); //on loading the page

            })
            .catch(error => {
                console.error('Impossible de charger les produits:', error);
            });
    }

    function fetchProductsByCategory(categoryId) {
        console.log("Fetching " + categoryId + "categories");

        if (!categoryId) return;


        let route = '/admin/menu/product/by-category/' + categoryId + '?productId=' + productId;

        fetch(route)
            .then(response => response.json())
            .then(data => {
                orderSelect.innerHTML = '';

                const currentProduct = data.current;
                const products = data.products;

                let initialCategoryIsDisplayed = (mode === "edit") ? (categoryId == currentProduct.category) : false;

                if (products.length > 0) {

                    let displayedProductIsLast = (mode === "edit") ? initialCategoryIsDisplayed && (currentProduct.id === products[products.length - 1].id) : false;

                    for (let index = 0; index < products.length; index++) {

                        let isDisplayedProduct = (mode === "edit") ? initialCategoryIsDisplayed && (currentProduct.id === products[index].id) : false;
                        console.log(isDisplayedProduct + " " + index);

                        if (isDisplayedProduct) {

                            continue;
                        }

                        const option = document.createElement('option');

                        if (initialCategoryIsDisplayed) {
                            if (index === 0) {
                                option.textContent = 'Au début de la catégorie';
                                option.textContent += `, avant "${products[0].title}"`;
                                option.value = products[index].position - 5;
                            }
                            else if (currentProduct.id === products[index - 1].id) { //if the last product in the array was the displayed product
                                if (index === 1) {
                                    option.textContent = 'Au début de la catégorie';
                                    option.textContent += `, avant "${products[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 5;

                                }
                                else if (index === products.length - 2) { //
                                    option.textContent = 'A la fin de la catégorie';
                                    option.textContent += `, après "${products[products.length - 2].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 10000;

                                }
                                else {
                                    option.textContent = `Entre "${products[index - 2].title}" et "${products[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;
                                    option.value = products[index].position - 5;
                                }
                            }
                            else {//middle

                                option.textContent = `Entre "${products[index - 1].title}" et "${products[index].title}"`;
                                option.value = products[index].position - 5;

                            }
                        }
                        else {
                            if (index === 0) { //first
                                option.textContent = 'Au début de la catégorie';
                                option.textContent += `, avant "${products[0].title}"`;
                                option.value = products[index].position - 5;
                            }
                            else {//middle

                                option.textContent = `Entre "${products[index - 1].title}" et "${products[index].title}"`;
                                option.value = products[index].position - 5;

                            }
                        }

                        orderSelect.appendChild(option);


                    }

                    //last option wwhich is not related to an existing index
                    const endOption = document.createElement('option');

                    if (displayedProductIsLast) {

                        if (products.length === 1) { //case where the only product in the category is the displayed product
                            const option = document.createElement('option');
                            option.value = 10;
                            option.textContent = "Ce produit est le seul produit de cette catégorie.";
                            option.selected = true;
                            orderSelect.appendChild(option);
                        }
                        else {
                            endOption.value = 10000;
                            endOption.textContent = 'A la fin de la catégorie';
                            endOption.textContent += `, après "${products[products.length - 2].title}"`;
                            endOption.textContent += ` (Position actuelle)`;

                            endOption.selected = true;
                            orderSelect.appendChild(endOption);
                        }
                    }
                    else {
                        endOption.value = 10000;
                        endOption.textContent = 'A la fin de la catégorie';
                        endOption.textContent += `, après "${products[products.length - 1].title}"`;
                        if (!initialCategoryIsDisplayed) endOption.selected = true;
                        orderSelect.appendChild(endOption);
                    }

                }
                else {

                    const option = document.createElement('option');
                    option.value = 10;
                    option.textContent = 'Cette catégorie est actuellement vide.';
                    option.selected = true;
                    orderSelect.appendChild(option);
                }

                console.log("Product from selected category loaded.");

            })
            .catch(error => {
                console.error('Impossible de charger les produits:', error);
            });
    }

    fetchCategories(); //fetch the categories on load

    categorySelect.addEventListener('change', function () { //everytime it's changed
        fetchProductsByCategory(this.value);
    });




    //#region Offer options

    document.querySelectorAll('.add-offer-bttn').forEach(btn => {
        btn.addEventListener("pointerdown", addFormToCollection)
    });

    document.querySelectorAll('.delete-offer-bttn').forEach(btn => {
        btn.addEventListener("pointerdown", removeFormFromCollection)
    });

    function addFormToCollection(e) {
        const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

        let newFormHtml = collectionHolder.dataset.prototype.replace(/__name__/g, collectionHolder.dataset.index); // Replace __name__ in the prototype

        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newFormHtml.trim(); ///to remove whitespace at the start and end of the string

        const newFormElement = tempDiv.firstChild; //we now extract the htlm element obtained from the string (so we can change classes and stuff)

        newFormElement.classList.add('admin-form__container__edit__row__offerItem');

        collectionHolder.appendChild(newFormElement);

        collectionHolder.dataset.index++;


        //add delete button
        const deleteBttn = document.createElement('i');
        deleteBttn.classList = "admin-form__container__edit__row__offerItem__deleteBttn delete-offer-bttn fa-solid fa-delete-left";
        newFormElement.appendChild(deleteBttn);
        deleteBttn.addEventListener("pointerdown", () => {
            newFormElement.remove();
        });

    };

    function removeFormFromCollection(e) {

        if (e.target.classList.contains('delete-offer-bttn')) {
            e.target.closest('.admin-form__container__edit__row__offerItem').remove();
        }

    }

    //#endregion






    //#region Changes detection

    // const form = document.querySelector('.admin-product-form__container'); //the form element
    // const button = document.querySelector('.admin-product-form__container__confirm-edit-bttn'); //the save button

    // const initialData = new FormData(form); //cache the initial form data

    // button.style.backgroundColor = 'blue';

    // function hasChanged() {
    //     const currentData = new FormData(form); //cache the current data

    //     for (let [key, value] of currentData.entries()) { //for each entry we check with the original
    //         if (initialData.get(key) !== value) {
    //             return true;
    //         }
    //     }

    //     // file input check
    //     const fileInput = form.querySelector('input[type="file"]');
    //     if (fileInput && fileInput.files.length > 0) {
    //         return true;
    //     }

    //     return false;
    // }

    // function toggleButton() {
    //     console.log(hasChanged());
    //     button.style.backgroundColor = hasChanged() ? 'red' : 'blue';
    // }

    // form.addEventListener('input', toggleButton);
    // form.addEventListener('change', toggleButton);

    //#endregion
}