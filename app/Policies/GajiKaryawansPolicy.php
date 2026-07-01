<?php

namespace App\Policies;

use App\Models\GajiKaryawans;
use App\Models\User;

/**
 * Otorisasi per-record untuk data gaji.
 *
 * Pondasi keamanan agar data gaji (rahasia) aman saat sistem nanti
 * diakses karyawan. Dipakai lewat Gate / @can / $this->authorize().
 * Laravel 11 menemukan policy ini otomatis (GajiKaryawans -> GajiKaryawansPolicy).
 */
class GajiKaryawansPolicy
{
    /**
     * Boleh melihat / mengunduh slip satu record gaji?
     * - admin/finance (view_all) -> boleh semua
     * - karyawan -> hanya miliknya sendiri
     */
    public function view(User $user, GajiKaryawans $gaji): bool
    {
        return $user->canViewAll('gajikaryawan')
            || (string) $gaji->nama_karyawan === (string) $user->id;
    }

    /**
     * Membuat/mengubah/menghapus gaji adalah aksi admin/finance,
     * dijaga oleh permission CRUD — karyawan tidak pernah punya.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create_gajikaryawan');
    }

    public function update(User $user, GajiKaryawans $gaji): bool
    {
        return $user->hasPermission('edit_gajikaryawan');
    }

    public function delete(User $user, GajiKaryawans $gaji): bool
    {
        return $user->hasPermission('delete_gajikaryawan');
    }
}
