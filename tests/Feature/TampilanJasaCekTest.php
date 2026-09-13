<?php

/**
 * Halaman /cek/{token} — hub jasa milik pelanggan.
 *
 * Diperiksa di SUMBER, bukan lewat render: menyiapkan pesanan jasa yang sah
 * butuh produk berkas, kuota per jenis, dan RagamJasa — biaya besar untuk
 * menjaga hal yang sebetulnya bersifat tekstual. Yang dijaga di sini justru dua
 * hal yang kalau rusak TIDAK menimbulkan galat apa pun:
 *
 * 1. Ketergantungan pada CSS beku. public/build masuk .gitignore, jadi gaya
 *    dari public-custom-styles.css tidak ikut `git pull` dan beku di server
 *    sampai ada rsync. Halaman yang meminjam kelas dari sana tampil rusak di
 *    produksi walau sempurna di lokal — tanpa satu pun galat di log.
 * 2. Kabel Livewire & id yang dipegang skrip. Kalau hilang, tombolnya cuma
 *    diam.
 */
$sumberJasaCek = fn () => file_get_contents(
    resource_path('views/livewire/pages/public/shop-page/jasa-cek-page.blade.php')
);

it('tidak lagi meminjam kerangka kartu dari CSS yang beku di server', function () use ($sumberJasaCek) {
    /*
     | Daftarnya SENGAJA tidak memuat .ph-page-title dan .ph-sec-eyebrow.
     |
     | Keduanya memang ditulis juga di public-custom-styles.css, tetapi aturan
     | di sana sudah lama DITIMPA oleh blok <style> di layouts/guest.blade.php —
     | yang ikut git dan ikut terdeploy. Kepala halaman karena itu aman memakai
     | markup baku, dan memang harus: dua belas halaman memakai markup yang
     | sama, dan satu yang berbeda akan terbaca sebagai halaman dari situs lain.
     */
    $sumber = $sumberJasaCek();

    foreach (['pay-card', 'pay-card-head', 'pay-card-body', 'cart-section', 'cart-summary-note',
        'ph-empty', 'ph-empty-title', 'ph-empty-sub', 'ph-empty-btn'] as $beku) {
        expect($sumber)->not->toContain('class="'.$beku.'"');
        expect($sumber)->not->toContain('class="'.$beku.' ');
    }
});

it('kepala halaman memakai markup baku, dengan aksen mengikuti jenis jasa', function () use ($sumberJasaCek) {
    $sumber = $sumberJasaCek();

    expect($sumber)->toContain('class="page-title ph-page-title"')
        ->and($sumber)->toContain('class="ph-page-head"')
        ->and($sumber)->toContain('class="breadcrumbs"')
        // --c adalah aksen yang disediakan layout untuk bola cahaya, titik-titik,
        // dan ubin ikon eyebrow. Tanpa ini kepalanya jingga tetap, dan pelanggan
        // parafrase dibacakan warna milik layanan pengecekan.
        ->and($sumber)->toContain("--c: {{ \$ragam['warna'] }}");
});

it('memakai kerangka kartu sendiri berikut ubin ikon berwarna', function () use ($sumberJasaCek) {
    $sumber = $sumberJasaCek();

    expect($sumber)->toContain('class="jck-page"')
        ->and($sumber)->toContain('class="jck-kartu"')
        ->and($sumber)->toContain('class="jck-kepala-kartu"')
        ->and($sumber)->toContain('class="jck-ubin"')
        ->and($sumber)->toContain('class="jck-btn-utama"');

    // Warna kartu unggah mengikuti JENIS JASA, bukan jingga tetap — pelanggan
    // parafrase tidak boleh dibacakan warna milik layanan pengecekan.
    expect($sumber)->toContain('--c: var(--kek-warna)');
});

it('variabel --ph-* didefinisikan sendiri, tidak menumpang berkas beku', function () use ($sumberJasaCek) {
    // Dipakai di puluhan tempat pada berkas ini. Kalau definisinya hanya ada di
    // public-custom-styles.css, seluruh garis dan warna teks sekunder halaman
    // ini akan hilang begitu berkas itu tertinggal versinya di server.
    $sumber = $sumberJasaCek();

    foreach (['--ph-orange:', '--ph-ink:', '--ph-muted:', '--ph-soft:', '--ph-line:'] as $token) {
        expect($sumber)->toContain($token);
    }
});

it('kabel Livewire & id yang dipegang skrip tetap utuh', function () use ($sumberJasaCek) {
    $sumber = $sumberJasaCek();

    expect($sumber)->toContain('wire:poll.20s="refreshStatus"')
        ->and($sumber)->toContain('wire:model="dokumen"')
        ->and($sumber)->toContain('wire:click="uploadDokumen"')
        ->and($sumber)->toContain('wire:target="uploadDokumen,dokumen"')
        // cekSalinLink() membaca elemen ini lewat id; kalau idnya berubah,
        // tombol "Salin" berhenti bekerja tanpa galat apa pun.
        ->and($sumber)->toContain('id="cek-permalink"')
        ->and($sumber)->toContain("getElementById('cek-permalink')");
});
