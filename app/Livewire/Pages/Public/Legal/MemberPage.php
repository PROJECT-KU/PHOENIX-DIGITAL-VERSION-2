<?php

namespace App\Livewire\Pages\Public\Legal;

use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman /member — keuntungan & syarat jadi member.
 *
 * Angka poin diambil dari SUMBERNYA (rumus di Customer), bukan diketik ulang
 * di view: kalau rumusnya berubah, halaman ini ikut benar dengan sendirinya.
 *   Customer::calculateYearlyPoints() -> floor($total / 50000)
 *   Customer::getPointValue()         -> $point * 500
 */
class MemberPage extends Component
{
    public const PER_POIN = 50000;

    public const NILAI_POIN = 500;

    /** Contoh belanja yang dipakai di bagian "contoh hitungan". */
    public const CONTOH_BELANJA = 170000;

    /** Tanggal perubahan ISI terakhir menurut riwayat git, bukan tata letak. */
    public const DIPERBARUI = '15 Juli 2026';

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.public.legal.member', [
            'perPoin' => self::PER_POIN,
            'nilaiPoin' => self::NILAI_POIN,
            'persenBalik' => round(self::NILAI_POIN / self::PER_POIN * 100, 1),
            'contohBelanja' => self::CONTOH_BELANJA,
            'contohPoin' => intdiv(self::CONTOH_BELANJA, self::PER_POIN),
            'contohSisa' => self::CONTOH_BELANJA % self::PER_POIN,
            'contohNilai' => intdiv(self::CONTOH_BELANJA, self::PER_POIN) * self::NILAI_POIN,
            'langkah' => self::langkah(),
            'keuntungan' => self::keuntungan(),
            'syarat' => self::syarat(),
            'bagian' => self::bagian(),
        ]);
    }

    /** Judul tiap bagian untuk daftar isi — urutannya sama dengan anchor mb-1..mb-4. */
    public static function bagian(): array
    {
        return [
            ['judul' => 'Caranya cuma 2 langkah', 'ikon' => 'bi-key', 'warna' => '#f26522'],
            ['judul' => 'Apa untungnya?', 'ikon' => 'bi-gift', 'warna' => '#16a34a'],
            ['judul' => 'Contoh hitungannya', 'ikon' => 'bi-calculator', 'warna' => '#2563eb'],
            ['judul' => 'Syarat & ketentuan', 'ikon' => 'bi-list-check', 'warna' => '#7c3aed'],
        ];
    }

    /** Dua langkah jadi member. */
    public static function langkah(): array
    {
        return [
            [
                'ikon' => 'bi-bag-check', 'warna' => '#f26522', 'judul' => 'Belanja',
                'teks' => 'Pesan produk apa saja, lalu tunggu sampai pesananmu berstatus <b>Selesai</b> — akun sudah kamu terima.',
            ],
            [
                'ikon' => 'bi-chat-heart', 'warna' => '#db2777', 'judul' => 'Tulis testimoni',
                'teks' => 'Ceritakan pengalamanmu lewat tombol <b>Tulis Testimoni</b> di halaman depan. Isi nomor WhatsApp yang sama dengan yang kamu pakai saat memesan.',
            ],
        ];
    }

    /** Keuntungan member. */
    public static function keuntungan(): array
    {
        return [
            [
                'ikon' => 'bi-coin', 'warna' => '#d97706', 'judul' => 'Poin belanja',
                'teks' => 'Tiap Rp '.number_format(self::PER_POIN, 0, ',', '.').' belanja jadi <b>1 poin</b>, dan 1 poin bernilai <b>Rp '
                    .number_format(self::NILAI_POIN, 0, ',', '.').'</b> potongan. Poin bisa dipakai kapan saja untuk memotong tagihan.',
                'chip' => '≈ '.rtrim(rtrim(number_format(self::NILAI_POIN / self::PER_POIN * 100, 1, ',', '.'), '0'), ',').'% belanja kembali',
            ],
            [
                'ikon' => 'bi-tags-fill', 'warna' => '#16a34a', 'judul' => 'Harga khusus member',
                'teks' => 'Banyak promo memberi diskon lebih besar untuk member, dan sebagian promo memang <b>hanya untuk member</b>.',
                'chip' => 'Diskon lebih besar',
            ],
            [
                'ikon' => 'bi-people-fill', 'warna' => '#2563eb', 'judul' => 'Kode referral',
                'teks' => 'Begitu jadi member, kamu langsung dapat <b>kode referral</b> untuk dibagikan ke teman — kalian berdua sama-sama untung.',
                'chip' => 'Berdua untung',
            ],
        ];
    }

    /** Syarat & ketentuan. */
    public static function syarat(): array
    {
        return [
            ['ikon' => 'bi-cash-coin', 'warna' => '#16a34a', 'teks' => 'Menjadi member <b>gratis</b> — tidak ada biaya pendaftaran maupun iuran.'],
            ['ikon' => 'bi-bag-check', 'warna' => '#f26522', 'teks' => 'Testimoni hanya bisa membuat kamu jadi member bila nomor WhatsApp-nya cocok dengan pesanan yang sudah berstatus <b>Selesai</b>. Pesanan yang masih menunggu pembayaran, sedang diproses, atau dibatalkan belum berlaku.'],
            ['ikon' => 'bi-chat-dots', 'warna' => '#db2777', 'teks' => 'Siapa pun tetap boleh menulis testimoni — hanya saja tanpa pesanan yang selesai, testimoninya tidak memberikan status member.'],
            ['ikon' => 'bi-shield-check', 'warna' => '#2563eb', 'teks' => 'Semua testimoni <b>ditinjau admin</b> terlebih dahulu. Testimoni yang tidak wajar dapat ditolak.'],
            ['ikon' => 'bi-calendar-event', 'warna' => '#d97706', 'teks' => '<b>Poin dihitung dari pesanan yang sudah dibayar</b> pada tahun berjalan, dan <b>direset setiap 1 Januari</b>. Pakai poinmu sebelum akhir tahun.'],
            ['ikon' => 'bi-lock-fill', 'warna' => '#7c3aed', 'teks' => 'Nomor WhatsApp-mu <b>tidak ditampilkan</b> pada testimoni dan tidak dibagikan ke pihak lain — hanya dipakai untuk mencocokkan pesanan.'],
        ];
    }
}
