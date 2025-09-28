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
const Swal2 = Swal.mixin({});

document.addEventListener("livewire:init", () => {
    // profile dan role event
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
    Livewire.on("added-role", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil tambah role baru",
        });
    });
    Livewire.on("updated-role", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil update data role",
        });
    });
    Livewire.on("deleted-role", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil hapus role",
        });
    });
    Livewire.on("failed-add-role", (data) => {
        Toast.fire({
            icon: "error",
            title: data.message || "gagal menambahkan role",
        });
    });
    Livewire.on("focus-input", () => {
        document.getElementById("name").focus();
    });
    Livewire.on("deleted-user", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil hapus data user",
        });
    });
    Livewire.on("user-role-updated", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil mengubah role user",
        });
    });
    // toast pengeluaran
    Livewire.on("success-add-pengeluaran", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menambah data pengeluaran",
        });
    });
    Livewire.on("failed-add-pengeluaran", (data) => {
        Toast.fire({
            icon: "error",
            title: data.message || "Gagal menambah data pengeluaran",
        });
    });
    Livewire.on("success-edit-pengeluaran", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil mengubah data pengeluaran",
        });
    });
    Livewire.on("failed-edit-pengeluaran", (data) => {
        Toast.fire({
            icon: "error",
            title: data.message || "Gagal mengubah data pengeluaran",
        });
    });
    Livewire.on("success-delete-pengeluaran", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menghapus data pengeluaran",
        });
    });
    Livewire.on("failed-delete-pengeluaran", (data) => {
        Toast.fire({
            icon: "error",
            title: data.message || "Gagal menghapus data pengeluaran",
        });
    });

    // customer event
    Livewire.on("customer-created", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menambah data pelanggan",
        });
    });
    Livewire.on("customer-updated", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil mengubah data pelanggan",
        });
    });
    Livewire.on("customer-deleted", (data) => {
        Toast.fire({
            icon: "success",
            title: data.message || "Berhasil menghapus data pelanggan",
        });
    });
    Livewire.on("will-delete-customer-data", (data) => {
        Swal2.fire({
            icon: "question",
            title: "Yakin ingin hapus data pelanggan " + data["nama"] + " ?",
            showCancelButton: true,
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("delete-customer", { id: data["id"] });
            }
        });
    });
    Livewire.on("will-delete-spending-data", (data) => {
        Swal2.fire({
            icon: "question",
            title: "Yakin ingin hapus data pengeluaran ini ?",
            showCancelButton: true,
            cancelButtonText: "Batal",
            confirmButtonText: "Ya, hapus",
        }).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch("delete-spending-data", { id: data["id"] });
            }
        });
    });
});
