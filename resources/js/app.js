import * as bootstrap from "bootstrap";
import "./bootstrap";

let currentYear = document.getElementById("current-year");
if (currentYear) {
    currentYear.textContent = new Date().getFullYear();
}
