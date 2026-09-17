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
