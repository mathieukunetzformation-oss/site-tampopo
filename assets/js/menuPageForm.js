export function inititializeMenuPageForm(mode) {

    console.log("Initializing " + mode + " page form");

    const orderSelect = document.querySelector('.order-select');

    if (!orderSelect) return;

    let pageId = null;

    const main = document.querySelector("main");

    if (mode === "new") console.log("New page form script.");


    pageId = main.dataset.pageId;

    function fetchPages(pageId) {
        console.log("Fetching pages");

        fetch('/admin/menu/page/fetchAll/' + "?pageId=" + pageId)
            .then(response => response.json())
            .then(data => {
                orderSelect.innerHTML = '';

                const displayedPage = data.displayedPage;
                const pages = data.pages;
                console.log(JSON.stringify(data, null, 2));

                if (pages.length > 0) {

                    let displayedPageIsLast = (mode === "edit") ? (displayedPage.id === pages[pages.length - 1].id) : false;

                    for (let index = 0; index < pages.length; index++) {

                        let isDisplayedPage = (mode === "edit") ? displayedPage.id === pages[index].id : false;

                        if (isDisplayedPage) {
                            continue;
                        }

                        const option = document.createElement('option');

                        if (index === 0) {
                            option.textContent = 'Au début du menu';
                            option.textContent += `, avant "${pages[0].title}"`;
                            option.value = pages[index].position - 5;
                        }
                        else if (mode === "edit") {
                            if (displayedPage.id === pages[index - 1].id) { //if the last category in the array was the displayed category
                                if (index === 1) {
                                    option.textContent = 'Au début du menu';
                                    option.textContent += `, avant "${pages[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 5;

                                }
                                else if (index === pages.length - 2) { //
                                    option.textContent = 'A la fin du menu';
                                    option.textContent += `, après "${pages[pages.length - 2].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;

                                    option.value = 10000;

                                }
                                else {
                                    option.textContent = `Entre "${pages[index - 2].title}" et "${pages[index].title}"`;
                                    option.textContent += ` (Position actuelle)`;
                                    option.selected = true;
                                    option.value = pages[index].position - 5;
                                }
                            }
                            else {//middle

                                option.textContent = `Entre "${pages[index - 1].title}" et "${pages[index].title}"`;
                                option.value = pages[index].position - 5;

                            }
                        }
                        else {//middle

                            option.textContent = `Entre "${pages[index - 1].title}" et "${pages[index].title}"`;
                            option.value = pages[index].position - 5;

                        }

                        orderSelect.appendChild(option);


                    }

                    //last option wwhich is not related to an existing index
                    const endOption = document.createElement('option');

                    if (displayedPageIsLast) {
                        if (pages.length === 1) { //case where the only product in the category is the displayed product
                            const option = document.createElement('option');
                            option.value = 10;
                            option.textContent = "Cette page est la seule page du menu.";
                            option.selected = true;
                            orderSelect.appendChild(option);
                        }
                        else {
                            endOption.value = 10000;
                            endOption.textContent = 'A la fin du menu';
                            endOption.textContent += `, après "${pages[pages.length - 2].title}"`;
                            endOption.textContent += ` (Position actuelle)`;

                            endOption.selected = true;
                            orderSelect.appendChild(endOption);

                        }

                    }
                    else {
                        endOption.value = 10000;
                        endOption.textContent = 'A la fin du menu';
                        endOption.textContent += `, après "${pages[pages.length - 1].title}"`;
                        if (mode === "new") endOption.selected = true;
                        orderSelect.appendChild(endOption);
                    }

                    console.log("Pages loaded.");

                }
                else {

                    const option = document.createElement('option');
                    option.value = 10;
                    option.textContent = "Aucune page n'a été trouvée.";
                    option.selected = true;
                    orderSelect.appendChild(option);

                    console.log("No pages found.");

                }


            })
            .catch(error => {
                console.error('Impossible de charger les catégories:', error);
            });
    }

    fetchPages(pageId); //fetch the categories on load

}