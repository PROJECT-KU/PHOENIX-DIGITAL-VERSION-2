import "./bootstrap";
import Swal from "sweetalert2";
// import featherIcons from "feather-icons";
// featherIcons.replace();

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
    Livewire.on("profile-updated", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil update data profil",
        });
    });
    Livewire.on("password-updated", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil update password",
        });
    });
    Livewire.on("photo-updated", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil update foto profil",
        });
    });
});
