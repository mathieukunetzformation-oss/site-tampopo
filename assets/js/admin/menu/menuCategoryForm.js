export function inititializeMenuCategoryForm(mode) {

    console.log("Initializing " + mode + " category form");

    const pageSelect = document.querySelector('.page-select');
    const orderSelect = document.querySelector('.order-select');

    if (!pageSelect || !orderSelect) return;

    let categoryId = null;
    let pageId = null;

    const main = document.querySelector("main");

    if (mode === "edit") {
        pageId = main.dataset.pageId;
        categoryId = main.dataset.categoryId;

    }
    else if (mode === "new") {
        pageId = main.dataset.pageId;
        console.log("New product form page script. PreFilled page :" + pageId);

    }


    function fetchPages() {

        console.log("Fetching pages");

        fetch('/admin/menu/page/fetch-all')
            .then(response => response.json())
            .then(data => {
                pageSelect.innerHTML = '';

                const pages = data.pages;

                pages.forEach(page => {

                    const option = document.createElement('option');
                    option.label = page.title;
                    option.value = page.id;

                    if (pageId === String(page.id)) {
                        console.log("Page selected =" + pageId);

                        option.selected = true;
                        if (mode === "edit") option.label += " (Page actuelle)";

                    }

                    pageSelect.appendChild(option);
                });

                console.log("Pages loaded");
                console.log("Page displayed =" + pageId)
                let pageDefaultSelectValue = pageId;

                fetchCategoriesByPage(pageDefaultSelectValue); //on loading the page

            })
            .catch(error => {
                console.error('Impossible de charger les pages:', error);
            });
    }

    function fetchCategoriesByPage(pageId) {
        console.log("Fetching " + pageId + " categories");

        if (!pageId) return;

        let route = '/admin/menu/category/fetch-by-page/' + pageId + '?categoryId=' + categoryId;

        fetch(route)
            .then(response => response.json())
            .then(data => {
                orderSelect.innerHTML = '';

                const displayedCategory = data.displayedCategory;
                const categories = data.categories;

                let initialPageIsDisplayed = (mode === "edit") ? (pageId == displayedCategory.page) : false;

                if (categories.length > 0) {

                    let displayedCategoryIsLast = (mode === "edit") ? initialPageIsDisplayed && (displayedCategory.id === categories[categories.length - 1].id) : false;

                    for (let index = 0; index < categories.length; index++) {

                        let isDisplayedCategory = (mode === "edit") ? initialPageIsDisplayed && (displayedCategory.id === categories[index].id) : false;

                        if (isDisplayedCategory) {
                            continue;
                        }

                        const option = document.createElement('option');

                        if (initialPageIsDisplayed) {
                            if (index === 0) {
                                option.textContent = 'Au début de la page';
                                option.textContent += `, avant "${categories[0].title}"`;
                                option.value = categories[index].position - 5;
                            }
                            else if (displayedCategory.id === categories[index - 1].id) { //if the last category in the array was the displayed category
                                if (index === 1) {
                                    option.textContent = 'Au début de la page';
                                    option.textContent += `, avant "${categories[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 5;

                                }
                                else if (index === categories.length - 2) { //
                                    option.textContent = 'A la fin de la page';
                                    option.textContent += `, après "${categories[categories.length - 2].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 10000;

                                }
                                else {
                                    option.textContent = `Entre "${categories[index - 2].title}" et "${categories[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;
                                    option.value = categories[index].position - 5;
                                }
                            }
                            else {//middle

                                option.textContent = `Entre "${categories[index - 1].title}" et "${categories[index].title}"`;
                                option.value = categories[index].position - 5;

                            }
                        }
                        else {
                            if (index === 0) { //first
                                option.textContent = 'Au début de la page';
                                option.textContent += `, avant "${categories[0].title}"`;
                                option.value = categories[index].position - 5;
                            }
                            else {//middle

                                option.textContent = `Entre "${categories[index - 1].title}" et "${categories[index].title}"`;
                                option.value = categories[index].position - 5;

                            }
                        }

                        orderSelect.appendChild(option);


                    }

                    //last option wwhich is not related to an existing index
                    const endOption = document.createElement('option');

                    if (displayedCategoryIsLast) {
                        if (categories.length === 1) { //case where the only product in the category is the displayed product
                            const option = document.createElement('option');
                            option.value = 10;
                            option.textContent = "Cette catégorie est la seule catégorie de cette page.";
                            option.selected = true;
                            orderSelect.appendChild(option);

                        } else {

                            endOption.value = 10000;
                            endOption.textContent = 'A la fin de la page';
                            endOption.textContent += `, après "${categories[categories.length - 2].title}"`;
                            endOption.textContent += ` (Position actuelle)`;

                            endOption.selected = true;
                            orderSelect.appendChild(endOption);
                        }

                    }
                    else {
                        endOption.value = 10000;
                        endOption.textContent = 'A la fin de la page';
                        endOption.textContent += `, après "${categories[categories.length - 1].title}"`;
                        if (!initialPageIsDisplayed) endOption.selected = true;
                        orderSelect.appendChild(endOption);
                    }

                }
                else {

                    const option = document.createElement('option');
                    option.value = 10;
                    option.textContent = 'Cette page est actuellement vide.';
                    option.selected = true;
                    orderSelect.appendChild(option);
                }

                console.log("Categories from selected page loaded.");

            })
            .catch(error => {
                console.error('Impossible de charger les catégories:', error);
            });
    }




    fetchPages(); //fetch the categories on load

    pageSelect.addEventListener('change', function () { //everytime it's changed
        fetchCategoriesByPage(this.value);
    });
}