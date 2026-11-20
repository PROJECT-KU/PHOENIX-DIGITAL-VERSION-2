<?php

namespace App\Livewire\Pages\Admin\Orcha\Rab;

use App\Exceptions\OrchaTidakTerjangkau;
use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Pengintip versi RAB: satu-satunya tugasnya memberi tahu kalau orang lain
 * baru saja mengubah RAB yang sedang dibuka.
 *
 * KENAPA KOMPONEN SENDIRI, BUKAN wire:poll DI LAYAR SUSUN
 *
 * Layar susun itu besar — itinerary, puluhan baris biaya, isian harga per
 * baris, panel nego, pemilih master harga. Setiap permintaan Livewire
 * membawa SELURUH keadaan komponen naik dan menggambar SELURUH halaman
 * turun. Dipasang di sana, pengintipan tiap 10 detik berarti muatan sebesar
 * halaman penuh dikirim dua arah tiap 10 detik, per tab, per admin.
 *
 * Hosting ini pernah menjatuhkan seluruh aplikasi karena itu: kotak putih
 * Livewire yang ternyata kuota proses hosting habis, dipicu poller tiap 5
 * detik yang terpasang di banyak tab sekaligus. Komponen ini sengaja dibuat
 * hampir tanpa keadaan — nomor RAB, satu angka versi, satu nama — supaya
 * yang berulang tiap 10 detik tetap sekecil mungkin.
 *
 * KENAPA BUKAN WEBSOCKET
 *
 * Itu yang sebetulnya pas untuk "langsung terbaca tanpa refresh", tetapi
 * BROADCAST_CONNECTION di server ini masih log dan hostingnya berbagi —
 * tidak ada proses yang boleh hidup terus untuk menunggui sambungan. Jadi
 * yang dipakai cara yang murah: Orcha menyimpan nomor versi yang naik satu
 * tiap perubahan, dan layar ini cuma menanyakan nomornya.
 */
class OrchaRabPenanda extends Component
{
    use MemanggilOrcha;

    public int $rabId = 0;

    /** Versi yang sudah terbaca di layar; 0 berarti Orcha belum mengirimnya. */
    public int $versi = 0;

    /** Terisi hanya kalau perubahannya datang dari ORANG LAIN. */
    public string $oleh = '';

    public string $kapan = '';

    public bool $berubah = false;

    public function mount(int $rabId, int $versi = 0): void
    {
        $this->rabId = $rabId;
        $this->versi = $versi;
    }

    /**
     * Satu panggilan ringan tiap 10 detik: tanya nomor versinya saja.
     *
     * Orcha yang belum dideploy tidak punya jalur /penanda dan akan membalas
     * 404. Itu bukan keadaan yang perlu dikabarkan ke admin — fiturnya cuma
     * belum ada di sana — jadi galatnya ditelan dan pengintipan berhenti
     * dengan sendirinya karena versinya tetap 0.
     */
    public function periksa(): void
    {
        try {
            $data = $this->orcha()->ambil("/rab/{$this->rabId}/penanda")['data'] ?? [];
        } catch (OrchaTidakTerjangkau) {
            return;
        }

        $versiBaru = (int) ($data['versi'] ?? 0);

        if ($versiBaru <= $this->versi) {
            return;
        }

        $this->versi = $versiBaru;

        /*
         | Perubahan SAYA SENDIRI ikut menaikkan versinya.
         |
         | Begitu saya menyimpan apa pun, nomornya naik, dan pengintip di tab
         | saya sendiri akan melihatnya berubah. Layar saya sudah memegang
         | data terbaru dari jawaban simpanan itu, jadi tidak ada yang perlu
         | dimuat ulang — dan bilah "Diubah oleh Budi" yang muncul di layar
         | Budi sendiri cuma membuatnya ragu apakah simpanannya jadi atau
         | tidak. Nomornya dicatat diam-diam, tanpa bilah.
         */
        if ($this->sayaSendiri((string) ($data['oleh'] ?? ''))) {
            return;
        }

        $this->oleh = trim((string) ($data['oleh'] ?? '')) ?: 'orang lain';
        $this->kapan = (string) ($data['pada_teks'] ?? '');
        $this->berubah = true;

        /*
         | Peramban yang memutuskan boleh-tidaknya memuat ulang sendiri.
         |
         | Hanya peramban yang tahu kursornya sedang di mana. Memuat ulang
         | saat admin sedang di tengah mengetik harga akan mengganti angka
         | yang sedang ia tulis — persis kerugian yang fitur ini mau cegah.
         | Jadi di sini cuma dikabarkan; skrip di layar susun yang memilih
         | antara memuat ulang seketika atau menunggu lewat bilah.
         */
        $this->dispatch('rab-diubah-orang-lain');
    }

    /**
     * Tombol "Muat ulang" di bilah.
     *
     * Bilahnya TIDAK dibereskan di sini, melainkan menunggu kabar
     * rab-sudah-dimuat dari layar susun. Kalau Orcha ternyata tidak bisa
     * dihubungi, layar susun gagal memuat dan datanya tetap yang lama —
     * bilah yang sudah hilang akan menyatakan layar ini mutakhir padahal
     * tidak, dan itu justru keadaan yang paling berbahaya di sini.
     */
    public function mintaMuatUlang(): void
    {
        $this->dispatch('rab-muat-ulang');
    }

    /** Layar susun sudah memuat data baru — bilahnya tidak perlu lagi. */
    #[On('rab-sudah-dimuat')]
    public function sudahDimuat(int $versi = 0): void
    {
        $this->berubah = false;
        $this->oleh = '';
        $this->kapan = '';

        if ($versi > $this->versi) {
            $this->versi = $versi;
        }
    }

    /**
     * Apakah nama yang dicatat Orcha itu saya?
     *
     * Dicocokkan dengan nama DAN surel karena itu dua nilai yang dikirim
     * OrchaClient sebagai X-Orcha-Admin: nama kalau ada, surel kalau nama
     * kosong. Dua admin bernama sama akan dianggap satu orang di sini;
     * akibatnya cuma bilah yang tidak muncul, dan data tetap aman karena
     * penolakan 409-nya tidak bergantung pada nama sama sekali.
     */
    private function sayaSendiri(string $oleh): bool
    {
        $oleh = trim($oleh);

        if ($oleh === '') {
            return false;
        }

        $saya = array_filter([auth()->user()?->name, auth()->user()?->email]);

        foreach ($saya as $nilai) {
            if (strcasecmp(trim((string) $nilai), $oleh) === 0) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        return view('livewire.pages.admin.orcha.rab.penanda');
    }
}
