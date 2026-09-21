<?php

use Livewire\Features\SupportMorphAwareIfStatement\SupportMorphAwareIfStatement;

/*
 * Penanda morph Livewire (<!--[if BLOCK]>) pada layar admin yang memakai
 * bahasa rupa dasbor.
 *
 * Livewire 3.6 memasang penanda di tiap direktif blok, tetapi melewatinya
 * bila (1) teks sesudah direktif sampai "<" berikutnya memuat ">" — termasuk
 * perbandingan "@if ($n > 0)" di direktif berikutnya, komentar Blade, dan
 * blok @php — atau (2) kondisi berkurung bersarang yang potongan awalnya
 * sama dengan direktif lebih awal di berkas (mis. dua "@if (auth()->…").
 * Penanda yang timpang tidak menggagalkan muat halaman, tetapi setiap
 * pembaruan Livewire sesudahnya rusak di Block.appendChild.
 *
 * Diuji pada SUMBER view (lihat catatan di UlasanProdukTest): view
 * terkompilasi di-cache dan dipakai bersama.
 */
function berkasPenandaAdmin(): array
{
    return collect([
        'pemesanan-r-s-c/*.blade.php',
        'order/order-list.blade.php',
        'order/order-detail.blade.php',
        'order/order-create.blade.php',
        'order/order-form.blade.php',
        'order/process-order.blade.php',
        'order/qris-payment.blade.php',
        'order/bukti-pembayaran.blade.php',
        'task/task-saya-list.blade.php',
        'task/partials/task-*.blade.php',
        'orcha/rab/*.blade.php',
        'blog/*.blade.php',
        'blog/partials/*.blade.php',
    ])->flatMap(fn ($pola) => glob(resource_path('views/livewire/pages/admin/'.$pola)))->values()->all();
}

it('penanda morph di layar admin selalu berpasangan', function () {
    $arah = [
        '@if' => '@endif', '@unless' => '@endunless', '@error' => '@enderror', '@isset' => '@endisset',
        '@auth' => '@endauth', '@guest' => '@endguest', '@switch' => '@endswitch',
        '@foreach' => '@endforeach', '@forelse' => '@endforelse', '@while' => '@endwhile', '@for' => '@endfor',
    ];
    foreach (array_keys(\Livewire\invade(app('blade.compiler'))->conditions) as $kondisi) {
        $arah['@'.$kondisi] = '@end'.$kondisi;
    }
    $nama = implode('|', array_map(fn ($d) => preg_quote(substr($d, 1), '/'), array_keys($arah)));
    $buka = '<!--[if BLOCK]><![endif]-->';
    $tutup = '<!--[if ENDBLOCK]><![endif]-->';

    $timpang = [];
    foreach (berkasPenandaAdmin() as $jalur) {
        $jadi = SupportMorphAwareIfStatement::compileDirectives(file_get_contents($jalur), $arah);

        // Pasangkan pembuka & penutup di hasil, lalu periksa bahwa keduanya
        // sama-sama bertanda atau sama-sama tidak. Pasangan yang sama-sama
        // dilewati (mis. @error di dalam atribut class) tidak berbahaya.
        preg_match_all('/\B@(end)?('.$nama.')(?![a-zA-Z])/', $jadi, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        $tumpuk = [];
        foreach ($m as $x) {
            [$teks, $pos] = $x[0];
            $baris = substr_count(substr($jadi, 0, $pos), "\n") + 1;
            if ($x[1][0] === '') {
                $tumpuk[] = [$baris, substr($jadi, max(0, $pos - strlen($buka)), strlen($buka)) === $buka, $teks];

                continue;
            }
            $pembuka = array_pop($tumpuk);
            $bertanda = substr($jadi, $pos + strlen($teks), strlen($tutup)) === $tutup;
            if (! $pembuka || $pembuka[1] !== $bertanda) {
                $timpang[] = basename($jalur).': '.($pembuka ? $pembuka[2].' (hasil baris '.$pembuka[0].')' : '?').' ↔ '.$teks.' (hasil baris '.$baris.')';
            }
        }
    }

    expect($timpang)->toBe([]);
});

it('elemen wire:loading di layar admin tidak membawa kelas display', function () {
    // Livewire menyembunyikan elemen wire:loading dengan CSS berbobot rendah
    // (0,2,0). Kelas yang mengatur display — d-inline-flex milik Bootstrap
    // (!important) atau kelas halaman berbobot sama yang dimuat sesudahnya —
    // mengalahkannya, sehingga spinner/"Menyimpan…" tampil terus. Tampilan
    // saat memuat harus diatur modifier Livewire (.flex, .inline-flex).
    $pelanggar = [];
    foreach (berkasPenandaAdmin() as $jalur) {
        $isi = file_get_contents($jalur);
        preg_match_all('/<[a-z]+\b[^>]*>/i', $isi, $tag, PREG_OFFSET_CAPTURE);
        foreach ($tag[0] as [$teks, $pos]) {
            // wire:loading.remove disembunyikan lewat gaya inline, jadi hanya
            // kelas !important (d-*) yang mengalahkannya; elemen wire:loading
            // biasa kalah oleh kelas display apa pun.
            $muat = preg_match('/\swire:loading[\s>=]/', $teks);
            $hapus = preg_match('/\swire:loading\.remove[\s>=]/', $teks);
            $kelasPenting = preg_match('/class="[^"]*\bd-(inline-flex|flex|block|inline|inline-block|grid)\b/', $teks);
            $kelasDisplay = $kelasPenting || preg_match('/class="[^"]*\bpcek-drop-state\b/', $teks);
            if (($muat && $kelasDisplay) || ($hapus && $kelasPenting)) {
                $pelanggar[] = basename($jalur).':'.(substr_count(substr($isi, 0, $pos), "\n") + 1);
            }
        }
    }

    expect($pelanggar)->toBe([]);
});

it('poller notifikasi tidak berdetak cepat di tab latar belakang', function () {
    // 5 detik nonstop termasuk di latar belakang berarti satu tab yang
    // dibiarkan terbuka mengirim 12 permintaan/menit selamanya; beberapa tab
    // sekaligus sesekali membuat hosting menjawab 503.
    $blade = file_get_contents(resource_path('views/livewire/layout/notif-poller.blade.php'));

    expect($blade)
        // Dasar yang tetap jalan walau Alpine gagal dimuat — tapi lambat.
        ->toContain('wire:poll.30s.keep-alive')
        ->not->toContain('wire:poll.5s')
        // Cepat hanya saat tabnya benar-benar dilihat.
        ->toContain('if (! document.hidden)')
        ->toContain('}, 5000);')
        // Interval dibersihkan saat komponennya dilepas (wire:navigate).
        ->toContain('destroy() { clearInterval(this.jeda); }');
});
