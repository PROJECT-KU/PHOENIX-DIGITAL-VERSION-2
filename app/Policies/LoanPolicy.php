<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

/**
 * Otorisasi per-record untuk data peminjaman.
 *
 * Peminjam dicocokkan lewat nama (nama_peminjam) karena kolom user_id pada
 * tabel loans menyimpan PENGINPUT, bukan peminjam. Dipakai lewat Gate/@can.
 */
class LoanPolicy
{
    public function view(User $user, Loan $loan): bool
    {
        return $user->canViewAll('loan')
            || $loan->nama_peminjam === $user->name;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create_loan');
    }

    public function update(User $user, Loan $loan): bool
    {
        return $user->hasPermission('edit_loan');
    }

    public function delete(User $user, Loan $loan): bool
    {
        return $user->hasPermission('delete_loan');
    }
}
