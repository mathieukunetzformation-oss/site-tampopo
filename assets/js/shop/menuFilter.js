

let filterBar;
let filterBarBttns;
let allPages;

export function initializeMenuFilter() {

    filterBarBttns = document.querySelectorAll(".filterBttn");
    allPages = document.querySelectorAll(".shopMenu__page");

    filterBarBttns.forEach(button => {
        button.addEventListener('click', () => {
            filterBarBttns.forEach(btn => btn.classList.remove("selected"));
            button.classList.add("selected");

            allPages.forEach(page => {
                if (button.dataset.pageId === "all") {
                    page.classList.toggle("hidden", false);
                }
                else if (page.dataset.pageId === button.dataset.pageId) {
                    page.classList.toggle("hidden", false);
                }
                else {
                    page.classList.toggle("hidden", true);

                }
            });
        });
    });
}