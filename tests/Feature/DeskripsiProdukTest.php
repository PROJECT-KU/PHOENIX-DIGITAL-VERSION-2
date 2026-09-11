<?php

use App\Support\DeskripsiProduk;

/**
 * Perapian deskripsi otomatis.
 *
 * Admin cukup mengetik atau menempel teks dari ChatGPT; tampilannya dirapikan
 * saat ditampilkan tanpa mengubah data tersimpan. Yang dijaga di sini: pola
 * toko yang sudah ada tetap terbaca sama, format tempelan AI dikenali, dan
 * tidak ada tebakan yang salah pada baris pertama.
 */
function jenisDeskripsi(array $blok): array
{
    return array_column($blok, 'jenis');
}

it('pola toko: baris judul, paragraf pembuka, lalu poin centang', function () {
    $blok = DeskripsiProduk::blok(
        "Scopus Lisensi + Scopus AI – Akses Siap Pakai\n\n"
        ."Tingkatkan riset akademikmu dengan Scopus! Akses database jurnal internasional terlengkap.\n\n"
        ."✅ Akses database Scopus lengkap\n✅ Termasuk Scopus AI untuk riset lebih cepat"
    );

    expect(jenisDeskripsi($blok))->toBe(['judul', 'paragraf', 'poin'])
        ->and($blok[0]['nama'])->toBe('Scopus Lisensi + Scopus AI')
        ->and($blok[0]['tagline'])->toBe('Akses Siap Pakai')
        ->and($blok[2]['butir'])->toBe(['Akses database Scopus lengkap', 'Termasuk Scopus AI untuk riset lebih cepat']);
});

it('paragraf panjang di baris pertama tidak ditebak sebagai judul', function () {
    // Diukur dari toko: judul 31–78 karakter, paragraf pembuka 146–329.
    $panjang = str_repeat('perangkat lunak pemetaan literatur berbasis kutipan ', 4);

    expect(jenisDeskripsi(DeskripsiProduk::blok($panjang."\n✅ Satu")))->toBe(['paragraf', 'poin']);
});

it('baris pendek yang diakhiri titik adalah kalimat, bukan judul', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok("Kahoot! adalah platform kuis.\n✅ Satu")))
        ->toBe(['paragraf', 'poin']);
});

it('satu baris saja tidak dijadikan judul', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok('Canva Premium – Akun Siap Pakai')))->toBe(['paragraf']);
});

it('teks tempelan ChatGPT dirapikan jadi bagian-bagian yang berurutan', function () {
    $blok = DeskripsiProduk::blok(
        "# Canva Premium – Akun Siap Pakai\n\n"
        ."Desain jadi lebih mudah dengan **Canva Premium**.\n\n"
        ."## Fitur Utama\n"
        ."- Template premium tanpa batas\n"
        ."- Hapus background **sekali klik**\n\n"
        ."## Cara Pakai\n"
        ."1. Selesaikan pembayaran\n"
        ."2. Akun dikirim ke email\n\n"
        ."---\n\n"
        .'📌 Catatan: Garansi selama masa aktif.'
    );

    expect(jenisDeskripsi($blok))->toBe(['judul', 'paragraf', 'subjudul', 'poin', 'subjudul', 'langkah', 'catatan'])
        ->and($blok[0]['tagline'])->toBe('Akun Siap Pakai')
        ->and($blok[2]['teks'])->toBe('Fitur Utama')
        ->and($blok[3]['butir'])->toBe(['Template premium tanpa batas', 'Hapus background **sekali klik**'])
        ->and($blok[5]['butir'])->toBe(['Selesaikan pembayaran', 'Akun dikirim ke email'])
        ->and($blok[6]['ikon'])->toBe('📌')
        ->and($blok[6]['teks'])->toBe('Catatan: Garansi selama masa aktif.');
});

it('label "Cocok untuk:" jadi subjudul dan tidak ditebak sebagai judul', function () {
    $blok = DeskripsiProduk::blok("Cocok untuk:\n- Mahasiswa\n- Dosen");

    expect(jenisDeskripsi($blok))->toBe(['subjudul', 'poin'])
        ->and($blok[0]['teks'])->toBe('Cocok untuk');
});

it('baris biasa di bawah "📌 Yang kamu dapat:" jadi daftar, bukan paragraf', function () {
    // Pola nyata di empat deskripsi paket: label ber-emoji, baris kosong,
    // lalu rincian isi paket tanpa penanda apa pun.
    $blok = DeskripsiProduk::blok(
        "📌 Yang kamu dapat:\n\nGrammarly & Consensus aktif 1 tahun\nPanduan login + garansi selama masa aktif\n\n"
        .'🎯 Cocok buat mahasiswa tingkat akhir.'
    );

    expect(jenisDeskripsi($blok))->toBe(['subjudul', 'poin', 'catatan'])
        ->and($blok[0]['ikon'])->toBe('📌')
        ->and($blok[0]['teks'])->toBe('Yang kamu dapat')
        ->and($blok[1]['butir'])->toBe(['Grammarly & Consensus aktif 1 tahun', 'Panduan login + garansi selama masa aktif']);
});

it('kalimat panjang sesudah label tetap paragraf dan menutup daftarnya', function () {
    $panjang = str_repeat('Penjelasan panjang yang jelas bukan butir daftar. ', 4);

    expect(jenisDeskripsi(DeskripsiProduk::blok("Cocok untuk:\n{$panjang}\nBaris berikutnya.")))
        ->toBe(['subjudul', 'paragraf', 'paragraf']);
});

it('gaya tulis admin Kahoot: judul bagian polos dan poin ber-emoji', function () {
    // Persis seperti yang ditulis admin: tanpa ##, tanpa titik dua, tanpa ✅,
    // tanpa baris kosong.
    $blok = DeskripsiProduk::blok(
        "Kahoot! adalah platform pembelajaran interaktif berbasis kuis yang membantu guru, dosen, dan perusahaan menciptakan pengalaman belajar yang lebih menarik.\n"
        ."Fitur Utama\n"
        ."🎮 Membuat kuis interaktif dengan mudah.\n"
        ."📊 Hasil dan laporan peserta secara real-time.\n"
        ."👥 Mendukung pembelajaran tatap muka maupun online.\n"
        .'🤖 Didukung fitur AI (pada paket tertentu).'
    );

    expect(jenisDeskripsi($blok))->toBe(['paragraf', 'subjudul', 'poin'])
        ->and($blok[1]['teks'])->toBe('Fitur Utama')
        ->and($blok[2]['ikon'])->toBe(['🎮', '📊', '👥', '🤖'])
        ->and($blok[2]['butir'][0])->toBe('Membuat kuis interaktif dengan mudah.');
});

it('emoji gabungan dan bervarian tetap utuh sebagai ikon poin', function () {
    $blok = DeskripsiProduk::blok("👨‍💻 Cocok untuk developer\n❤️ Disukai pengguna");

    expect($blok[0]['ikon'])->toBe(['👨‍💻', '❤️'])
        ->and($blok[0]['butir'])->toBe(['Cocok untuk developer', 'Disukai pengguna']);
});

it('emoji di tengah kalimat tidak memecah kalimatnya', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok("Belajar jadi seru 🎮 bareng teman.\nParagraf kedua.")))
        ->toBe(['paragraf', 'paragraf']);
});

it('baris pertama ber-slogan di atas daftar tetap jadi judul produk', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok("Canva Premium – Akun Siap Pakai\n✅ Satu\n✅ Dua")))
        ->toBe(['judul', 'poin']);
});

it('baris pendek yang tidak diikuti daftar tetap paragraf', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok("Pembuka yang panjang dan diakhiri titik.\nSiap pakai\nPenutup.")))
        ->toBe(['paragraf', 'paragraf', 'paragraf']);
});

it('baris tebal berupa pertanyaan tetap subjudul, bukan judul', function () {
    expect(jenisDeskripsi(DeskripsiProduk::blok("**Kenapa pilih kami?**\n- Proses cepat")))
        ->toBe(['subjudul', 'poin']);
});

it('penanda sebaris gaya lama tetap dipecah jadi poin', function () {
    $blok = DeskripsiProduk::blok('✅ Fitur A ✅ Fitur B ✅ Fitur C');

    expect(jenisDeskripsi($blok))->toBe(['poin'])
        ->and($blok[0]['butir'])->toBe(['Fitur A', 'Fitur B', 'Fitur C']);
});

it('baris lanjutan menempel ke poin sebelumnya', function () {
    // AI kerap membungkus poin panjang jadi dua baris.
    $blok = DeskripsiProduk::blok("✅ Garansi selama masa aktif\n(sesuai ketentuan)");

    expect($blok[0]['butir'])->toBe(['Garansi selama masa aktif (sesuai ketentuan)']);
});

it('teks di-escape sebelum tebal diubah jadi strong', function () {
    expect(DeskripsiProduk::inline('<script>x</script> **tebal** [klik](https://contoh.test)')->toHtml())
        ->toBe('&lt;script&gt;x&lt;/script&gt; <strong>tebal</strong> klik');
});

it('akhir baris Windows dan tanda BOM dari teks tempelan dibersihkan', function () {
    $blok = DeskripsiProduk::blok("\u{FEFF}Canva Premium – Akun Siap Pakai\r\n\r\nParagraf pembuka yang diakhiri titik.\r\n✅ Satu");

    expect(jenisDeskripsi($blok))->toBe(['judul', 'paragraf', 'poin']);
});

it('pisah() tetap memberi kunci lama dan judul tidak lagi dicetak sebagai paragraf', function () {
    $hasil = DeskripsiProduk::pisah("Canva Premium – Akun Siap Pakai\n\nParagraf pembuka.\n\n✅ Satu\n📌 Catatan penting");

    expect(array_keys($hasil))->toBe(['judul', 'tagline', 'paragraf', 'poin', 'ekstra'])
        ->and($hasil['judul'])->toBe('Canva Premium – Akun Siap Pakai')
        ->and($hasil['paragraf'])->toBe(['Paragraf pembuka.'])
        ->and($hasil['poin'])->toBe(['Satu'])
        ->and($hasil['ekstra'])->toBe([['ikon' => '📌', 'teks' => 'Catatan penting']]);
});

it('form admin menampilkan pratinjau dari partial yang sama dengan halaman toko', function () {
    // Dicari lewat atribut class yang lengkap: nama kelasnya juga muncul di
    // blok <style> yang selalu dirender.
    Livewire\Livewire::test(App\Livewire\Pages\Admin\Product\ProductForm::class)
        ->assertSeeHtml('class="dr-pratinjau-kosong"')
        ->set('deskripsi', "Canva Premium – Akun Siap Pakai\n\nDesain jadi mudah.\n\n- Template premium")
        ->assertSeeHtml('class="dr-tagline"')
        ->assertSeeHtml('class="dr-poin"')
        ->assertDontSeeHtml('class="dr-pratinjau-kosong"');
});

it('deskripsi kosong menghasilkan daftar kosong', function () {
    expect(DeskripsiProduk::blok(null))->toBe([])
        ->and(DeskripsiProduk::blok("  \n\n  "))->toBe([]);
});
