<?php

use App\Models\OrderUpload;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Pesanan PARAFRASE yang dibuat SEBELUM fitur pengecualian bagian dokumen
     * masih memakai setelan bawaan cek plagiasi (Daftar Pustaka + Kutipan) dan
     * belum menyalin nomor halaman yang diminta customer.
     *
     * Isi mundur agar tim melihat instruksi yang benar:
     *  - Cover, Daftar Isi, Daftar Pustaka dikecualikan (default parafrase)
     *  - Kutipan / Sumber kecil dimatikan (tak relevan untuk parafrase)
     *  - Nomor halaman disalin dari item pesanan
     *
     * Aman: hanya menyentuh baris LAMA (dibuat sebelum migrasi ini) yang belum
     * pernah disentuh alur baru, jadi pilihan customer setelahnya tidak tertimpa.
     */
    public function up(): void
    {
        $batas = now();

        OrderUpload::query()
            ->whereNull('halaman_dikecualikan')
            ->where('exclude_cover', false)
            ->where('exclude_daftar_isi', false)
            ->where('created_at', '<=', $batas)
            ->whereHas('order.items.product', fn ($q) => $q->where('jasa_mode', 'halaman'))
            ->with('order.items.product')
            ->chunkById(100, function ($uploads) {
                foreach ($uploads as $upload) {
                    // Ambil instruksi halaman dari item jasa per halaman pesanan ini.
                    $item = $upload->order?->items
                        ->first(fn ($i) => optional($i->product)->jasa_mode === 'halaman');

                    $upload->update([
                        'exclude_cover' => true,
                        'exclude_daftar_isi' => true,
                        'exclude_bibliografi' => true,
                        'exclude_kutipan' => false,
                        'exclude_sumber_kecil' => false,
                        'halaman_dikecualikan' => $item?->halaman_dikecualikan,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Tidak dikembalikan: instruksi yang benar sebaiknya tetap ada.
    }
};
