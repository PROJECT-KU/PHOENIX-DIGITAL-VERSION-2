import * as bootstrap from "bootstrap";
import "./bootstrap";
import Swal from "sweetalert2";

let currentYear = document.getElementById("current-year");
if (currentYear) {
    currentYear.textContent = new Date().getFullYear();
}

// Setup Toast sekali saja
const Toast = Swal.mixin({
    toast: true,
    position: "top-end",
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

document.addEventListener("livewire:init", () => {
    Livewire.on("login-error", (data) => {
        Toast.fire({
            icon: "error",
            title: data.message || "Email atau password salah",
        });
    });
});

