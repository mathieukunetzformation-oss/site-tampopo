let dateInput;
let dateError;

export function initializeReservationIndex() {

    dateInput = document.getElementById("viewDate");
    dateError = document.getElementById("dateError");

    dateInput.addEventListener("change", function () {
        console.log("Date changed to:", this.value);
        if (this.value) {
            dateError.classList.toggle("hidden", true);

            fetchReservationByDate(this.value); //add a date verification before passing it to the function
        }
        else {
            dateError.classList.toggle("hidden", false);
        }
    })

    dateError.classList.toggle("hidden", true);


}

function fetchReservationByDate(dateString) {

    let formatedDate = dateString;

    fetch('/admin/reservation/by-date/' + formatedDate)
        .then(response => response.json())
        .then(data => {

            console.log(data);

        })
        .catch(error => {
            console.error('Impossible de charger les reservations:', error);
        });
}