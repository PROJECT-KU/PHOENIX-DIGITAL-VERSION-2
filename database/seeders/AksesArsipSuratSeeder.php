<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Surat\SuratKlasifikasi;
use App\Support\Surat\ModeSurat;
use Illuminate\Database\Seeder;

/**
 * Mode Arsip Surat — padanan AksesOrchaSeeder & AksesAsthanaSeeder.
 *
 *   php artisan db:seed --class=AksesArsipSuratSeeder --force
 *
 * 1. Membuat izin gerbang 'akses_arsip_surat' dan izin per layarnya.
 * 2. Memberikannya HANYA kepada peran yang memegang 'manage_roles'.
 * 3. Mengisi klasifikasi surat bawaan supaya layarnya tidak kosong di hari
 *    pertama.
 *
 * KENAPA HANYA PEMEGANG manage_roles
 *
 * Dua seeder sebelumnya memberi izin gerbang kepada siapa pun yang sudah
 * memegang izin layar yang dipindah ke mode baru — benar di sana, karena
 * layarnya sudah ada lebih dulu dan mereka akan terkunci dari layar yang
 * kemarin masih terbuka. Di sini layarnya BARU: tidak ada yang kehilangan
 * apa pun, dan arsip surat memuat surat rahasia serta nama orang. Menebak
 * siapa yang boleh membacanya lebih buruk daripada meminta admin
 * memberikannya sendiri di Role → Permission.
 *
 * Nama peran di produksi tidak sama dengan di seeder (Admin, administrator,
 * karyawan), jadi penerimanya dipilih lewat IZIN yang dipegang, bukan lewat
 * nama perannya.
 *
 * Aman dijalankan berulang (firstOrCreate, syncWithoutDetaching).
 */
class AksesArsipSuratSeeder extends Seeder
{
    public function run(): void
    {
        $izin = [
            'akses_arsip_surat' => [
                'Akses Mode Arsip Surat',
                'Dapat berpindah ke mode Arsip Surat: buku agenda surat masuk & keluar. Izin per layar tetap berlaku di dalamnya.',
            ],
            'view_surat' => ['Lihat Arsip Surat', 'Dapat melihat daftar dan detail surat — kecuali yang bersifat rahasia.'],
            'view_surat_rahasia' => [
                'Lihat Surat Rahasia',
                'Dapat melihat surat yang ditandai RAHASIA beserta pindaiannya. Berikan hanya kepada yang benar-benar perlu — di dalamnya ada hasil audit, teguran, dan perjanjian.',
            ],
            'create_surat' => ['Catat Surat', 'Dapat mencatat surat masuk/keluar baru dan menambah disposisi.'],
            'edit_surat' => ['Ubah Surat', 'Dapat mengubah data surat, status, dan lampirannya.'],
            'delete_surat' => ['Hapus Surat', 'Dapat menghapus surat dari arsip.'],
            'kelola_klasifikasi_surat' => [
                'Kelola Klasifikasi Surat',
                'Dapat menambah/mengubah kode klasifikasi surat dan masa simpannya.',
            ],
        ];

        $dibuat = [];

        foreach ($izin as $nama => [$label, $keterangan]) {
            $dibuat[$nama] = Permission::firstOrCreate(['name' => $nama], [
                'display_name' => $label,
                'group' => 'arsip_surat',
                'description' => $keterangan,
            ]);
        }

        // Grupnya dipaksa juga untuk izin yang sudah ada dari jalan lain,
        // supaya seluruhnya tampil satu blok di Role → Permission.
        Permission::whereIn('name', ModeSurat::IZIN)->update(['group' => 'arsip_surat']);

        $semuaId = collect($dibuat)->pluck('id')->all();

        Role::whereHas('permissions', fn ($q) => $q->where('permissions.name', 'manage_roles'))
            ->get()
            ->each(fn (Role $r) => $r->permissions()->syncWithoutDetaching($semuaId));

        $this->klasifikasiBawaan();
    }

    /**
     * Klasifikasi bawaan + masa simpannya.
     *
     * Kodenya mengikuti pola tiga angka yang lazim di tata naskah pemerintah
     * dan sudah dikenal staf administrasi, tetapi NAMANYA ditulis dalam
     * kalimat biasa — "Keuangan & Pembayaran", bukan "Keu.01". Admin yang
     * awam memilih dari kalimat, bukan dari kode.
     *
     * Masa simpannya angka yang bisa diubah admin kemudian; yang penting
     * tidak ada baris yang lahir tanpa masa simpan sama sekali, karena kolom
     * kosong itulah yang kemudian tidak pernah diisi.
     */
    private function klasifikasiBawaan(): void
    {
        $daftar = [
            ['000', 'Umum & Ketatausahaan', 'Undangan, edaran, pengumuman, surat pengantar.', 2, 10],
            ['100', 'Pemerintahan & Perizinan', 'Surat dari instansi, izin usaha, legalitas.', 10, 20],
            ['200', 'Kerja Sama & Perjanjian', 'MoU, kontrak kerja sama, penawaran resmi.', 10, 30],
            ['300', 'Kepegawaian', 'Lamaran, SK, mutasi, cuti, teguran.', 10, 40],
            ['400', 'Keuangan & Pembayaran', 'Tagihan, kuitansi, pajak, penagihan.', 10, 50],
            ['500', 'Pengadaan & Perlengkapan', 'Pembelian barang, inventaris, perbaikan.', 5, 60],
            ['600', 'Pelanggan & Pengaduan', 'Keluhan, permintaan data, klaim.', 5, 70],
            ['900', 'Lain-lain', 'Belum masuk kelompok mana pun.', null, 90],
        ];

        foreach ($daftar as [$kode, $nama, $keterangan, $retensi, $urutan]) {
            SuratKlasifikasi::firstOrCreate(['kode' => $kode], [
                'nama' => $nama,
                'keterangan' => $keterangan,
                'retensi_tahun' => $retensi,
                'aktif' => true,
                'urutan' => $urutan,
            ]);
        }
    }
}
