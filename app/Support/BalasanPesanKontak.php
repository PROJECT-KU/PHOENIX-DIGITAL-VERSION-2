<?php

namespace App\Support;

/**
 * Balasan WhatsApp untuk satu pesan kontak.
 *
 * Sebelumnya ada dua balasan yang berbeda untuk pesan yang sama: daftar
 * mengirim satu kalimat basa-basi ("terima kasih sudah menghubungi"), halaman
 * detail mengirim kutipan pertanyaannya. Keduanya sama-sama tidak menjawab
 * apa pun — pelanggan yang bertanya soal study tour dan yang bertanya soal
 * sewa kendaraan menerima kalimat yang persis sama, lalu harus menunggu satu
 * putaran lagi hanya untuk ditanyai hal yang sudah bisa ditanyakan sejak awal.
 *
 * Sekarang balasannya mengikuti keperluannya: tiap jenis pertanyaan punya
 * daftar hal yang memang selalu perlu diketahui sebelum penawaran bisa
 * disusun. Satu balasan pertama yang menanyakan semuanya sekaligus memotong
 * percakapan yang biasanya tiga-empat kali bolak-balik.
 *
 * Dikumpulkan di sini, bukan di komponen, supaya daftar dan detail benar-benar
 * mengirim kalimat yang sama — bukan dua salinan yang lambat laun berbeda.
 */
class BalasanPesanKontak
{
    /**
     * Yang perlu ditanyakan lebih dulu, per keperluan.
     *
     * Isinya bukan karangan: ini pertanyaan yang toh selalu ditanyakan admin
     * pada balasan kedua atau ketiga.
     */
    /**
     * Nama perihal yang bisa disambung jadi kalimat.
     *
     * Label bawaannya adalah teks pilihan di formulir, bukan kata benda:
     * "Tanya Open Trip", "Kerja Sama / Partner", "Lainnya". Dipasang apa
     * adanya, kalimatnya jadi "menghubungi Orcha Journey soal Tanya Open Trip"
     * — dan untuk "Lainnya" malah tidak berarti apa-apa.
     */
    private const PERIHAL = [
        'open_trip' => 'Open Trip',
        'private_trip' => 'Private Trip',
        'study_tour' => 'Study Tour',
        'sewa_kendaraan' => 'sewa kendaraan',
        'kerja_sama' => 'kerja sama',
        'lainnya' => null,
    ];

    private const LANJUTAN = [
        'open_trip' => [
            'Supaya bisa kami carikan jadwal yang pas, boleh dibantu:',
            ['Tujuan yang diminati', 'Perkiraan tanggal berangkat', 'Jumlah peserta'],
            'Nanti kami kirimkan jadwal terdekat, harga per orang, dan apa saja yang sudah termasuk.',
        ],
        'private_trip' => [
            'Private trip disusun mengikuti maunya rombongan, jadi boleh dibantu:',
            ['Tujuan yang diinginkan', 'Tanggal dan berapa hari', 'Jumlah peserta', 'Titik jemput'],
            'Nanti kami susunkan rencana perjalanan beserta rincian biayanya.',
        ],
        'study_tour' => [
            'Untuk study tour, penawarannya kami susun per rombongan. Boleh dibantu:',
            ['Nama sekolah atau instansi', 'Jumlah siswa dan pendamping', 'Tanggal rencana keberangkatan', 'Tujuan atau tema kunjungan'],
            'Nanti kami kirimkan penawaran lengkap: rincian biaya per siswa, fasilitas, dan susunan acaranya.',
        ],
        'sewa_kendaraan' => [
            'Supaya bisa kami cek ketersediaan unitnya, boleh dibantu:',
            ['Jenis kendaraan yang dibutuhkan', 'Tanggal mulai dan lama sewa', 'Tujuan — dalam kota atau luar kota', 'Perlu sopir atau lepas kunci'],
            'Nanti kami kirimkan unit yang tersedia beserta rincian biaya dan apa saja yang sudah termasuk.',
        ],
        'kerja_sama' => [
            'Untuk kerja sama, boleh dibantu:',
            ['Nama perusahaan, komunitas, atau media', 'Bentuk kerja sama yang dimaksud'],
            'Nanti kami teruskan ke bagian yang menangani supaya dibalas langsung oleh yang berwenang.',
        ],
    ];

    /**
     * Balasan siap kirim, memakai penanda emoji [[E:xxxx]].
     *
     * Penandanya dirakit di peramban — emoji yang ditulis langsung di PHP
     * sampai dalam keadaan rusak lewat sebagian jalur salin-tempel.
     */
    public static function untuk(array $pesan): string
    {
        $nama = trim((string) ($pesan['nama'] ?? '')) ?: 'Kak';
        $keperluan = (string) ($pesan['keperluan'] ?? 'lainnya');
        $perihal = self::perihal($keperluan, $pesan['keperluan_label'] ?? null);

        $isi = trim((string) ($pesan['pesan'] ?? ''));
        $kutipan = mb_strlen($isi) > 120 ? mb_substr($isi, 0, 117).'…' : $isi;

        $teks = "Halo {$nama} [[E:1F44B]]\n\n"
            .'Terima kasih sudah menghubungi *Orcha Journey*'
            .($perihal ? " soal {$perihal}" : '').".\n\n";

        if ($kutipan !== '') {
            $teks .= "Menanggapi pesan Anda:\n_\"{$kutipan}\"_\n\n";
        }

        // "Lainnya" sengaja tidak dipandu: pertanyaannya bisa apa saja, dan
        // menebak-nebak daftar isian justru membuat balasannya terasa salah
        // alamat.
        if (! isset(self::LANJUTAN[$keperluan])) {
            return $teks.'Ada yang bisa kami bantu jelaskan lebih dulu?';
        }

        [$pembuka, $butir, $penutup] = self::LANJUTAN[$keperluan];

        $teks .= $pembuka."\n";

        foreach ($butir as $satu) {
            // Tanda hubung biasa, bukan penanda emoji: tautan WhatsApp
            // melepas semua penanda, jadi butir yang dirakit lewat penanda
            // akan hilang titiknya di satu jalur dan tidak di jalur lain.
            $teks .= "- {$satu}\n";
        }

        return $teks."\n".$penutup;
    }

    /**
     * Perihal untuk disebut di kalimat pembuka, atau null bila tidak ada yang
     * pantas disebut.
     *
     * Petanya dikunci pada kode keperluan, bukan labelnya, karena kode itulah
     * yang stabil. Tetapi keperluan baru bisa muncul di Orcha kapan saja tanpa
     * berkas ini ikut diubah — jadi bila kodenya belum dikenal, labelnya
     * dipakai setelah awalan "Tanya " dilepas.
     */
    private static function perihal(string $keperluan, ?string $label): ?string
    {
        if (array_key_exists($keperluan, self::PERIHAL)) {
            return self::PERIHAL[$keperluan];
        }

        $bersih = trim(preg_replace('/^Tanya\s+/i', '', (string) $label));

        return $bersih !== '' ? $bersih : null;
    }

    /** Tautan WhatsApp siap klik; penanda emoji dilepas karena tidak dirakit di sana. */
    public static function tautan(array $pesan): string
    {
        return TautanWa::kirim($pesan['whatsapp'] ?? null, self::polos($pesan));
    }

    /** Balasan tanpa penanda emoji. */
    public static function polos(array $pesan): string
    {
        $teks = preg_replace('/\[\[E:[0-9A-F]+\]\] ?/', '', self::untuk($pesan));

        // Penanda yang dilepas meninggalkan spasi menggantung di ujung baris.
        // Tidak terlihat di layar, tetapi ikut terbawa ke kotak ketik WhatsApp.
        return preg_replace('/[ \t]+$/m', '', $teks);
    }
}
