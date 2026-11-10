<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Asthana\Berita;
use App\Models\Asthana\Klien;
use App\Models\Asthana\Portofolio;
use App\Models\Asthana\Tim;
use App\Support\Asthana\BeritaOrcha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Isi situs profil PT. Asthana Cipta Mandiri (baca saja, publik).
 *
 * Hanya mengirim yang sudah ditandai tampil/terbit. Draf, berita terjadwal,
 * dan butir yang disembunyikan tidak pernah keluar dari sini.
 */
class SitusAsthanaController extends Controller
{
    use ApiResponse;

    private const PER_HALAMAN_BERITA = 9;

    /**
     * Semua isi beranda dalam SATU permintaan.
     *
     * Rute publiknya dibatasi 20 permintaan/menit per IP; lima permintaan
     * terpisah per kunjungan beranda akan menghabiskan jatah itu dalam empat
     * kali muat ulang.
     */
    public function beranda(): JsonResponse
    {
        return $this->ok([
            'klien' => Klien::tampil()->get()->map(fn (Klien $k) => [
                'nama' => $k->nama,
                'logo_url' => Klien::urlGambar($k->logo),
                'tautan' => $k->tautan,
            ]),
            // Asthana + testimoni tayang Orcha (+ Reka, segera hadir) — lihat TestimoniGrup.
            'testimoni' => \App\Support\Asthana\TestimoniGrup::semua(),
            'portofolio' => Portofolio::terbit()->where('unggulan', true)->urutSitus()->limit(3)->get()
                ->map(fn (Portofolio $p) => $p->keKartu()),
            // Berita Asthana + artikel blog Orcha yang sudah tayang.
            'berita' => BeritaOrcha::gabungan()->take(3)->map(fn ($b) => self::tanpaInternal($b))->values(),
        ]);
    }

    /**
     * Jumlah isi terbit per bagian — situs menyembunyikan menu Portofolio &
     * Berita sampai isinya minimal satu.
     */
    public function ringkas(): JsonResponse
    {
        return $this->ok([
            'portofolio' => Portofolio::terbit()->count(),
            'berita' => BeritaOrcha::gabungan()->count(),
        ]);
    }

    /** Isi tambahan halaman Karir: kutipan karyawan. */
    public function karir(): JsonResponse
    {
        return $this->ok([
            'suara_karyawan' => \App\Models\Asthana\SuaraKaryawan::tampil()->get()->map(fn ($s) => [
                'nama' => $s->nama,
                'jabatan' => $s->jabatan,
                'perusahaan' => $s->perusahaan,
                'isi' => $s->isi,
                'foto_url' => \App\Models\Asthana\SuaraKaryawan::urlGambar($s->foto),
            ]),
        ]);
    }

    public function tim(): JsonResponse
    {
        // id & atasan_id: situs menggambar bagan struktur organisasi darinya.
        return $this->ok(Tim::tampil()->get()->map(fn (Tim $t) => [
            'id' => $t->id,
            'atasan_id' => $t->atasan_id,
            'nama' => $t->nama,
            'jabatan' => $t->jabatan,
            'foto_url' => Tim::urlGambar($t->foto),
            'linkedin' => $t->linkedin,
        ]));
    }

    public function portofolio(Request $request): JsonResponse
    {
        $kategori = $request->query('kategori');

        $daftar = Portofolio::terbit()
            ->when(isset(Portofolio::KATEGORI[$kategori]), fn ($q) => $q->where('kategori', $kategori))
            ->urutSitus()
            ->get();

        return $this->ok($daftar->map(fn (Portofolio $p) => $p->keKartu()), meta: [
            'kategori' => Portofolio::KATEGORI,
        ]);
    }

    public function portofolioDetail(string $slug): JsonResponse
    {
        $p = Portofolio::terbit()->where('slug', $slug)->first();

        if (! $p) {
            return $this->fail('Portofolio tidak ditemukan.', status: 404);
        }

        $lainnya = Portofolio::terbit()->whereKeyNot($p->id)->where('kategori', $p->kategori)
            ->urutSitus()->limit(3)->get()->map(fn (Portofolio $x) => $x->keKartu());

        return $this->ok($p->keDetail() + ['lainnya' => $lainnya]);
    }

    public function berita(Request $request): JsonResponse
    {
        // Berita Asthana + artikel blog Orcha (lihat BeritaOrcha), dipaginasi
        // di sini karena sumbernya dua.
        $semua = BeritaOrcha::gabungan();
        $terakhir = max(1, (int) ceil($semua->count() / self::PER_HALAMAN_BERITA));
        $halaman = min($terakhir, max(1, (int) $request->query('halaman', 1)));

        return $this->ok(
            $semua->forPage($halaman, self::PER_HALAMAN_BERITA)->map(fn ($b) => self::tanpaInternal($b))->values(),
            meta: [
                'halaman' => $halaman,
                'halaman_terakhir' => $terakhir,
                'total' => $semua->count(),
            ],
        );
    }

    /** Buang kunci internal (id_orcha) sebelum dikirim ke situs. */
    private static function tanpaInternal(array $b): array
    {
        unset($b['id_orcha']);

        return $b;
    }

    public function beritaDetail(string $slug): JsonResponse
    {
        if (BeritaOrcha::milikOrcha($slug)) {
            $detail = BeritaOrcha::detail($slug);
        } else {
            $b = Berita::terbit()->where('slug', $slug)->first();
            $detail = $b ? $b->keDetail() + ['sumber' => 'asthana'] : null;
        }

        if (! $detail) {
            return $this->fail('Berita tidak ditemukan.', status: 404);
        }

        $lainnya = BeritaOrcha::gabungan()->where('slug', '!=', $slug)->take(3)
            ->map(fn ($x) => self::tanpaInternal($x))->values();

        return $this->ok(self::tanpaInternal($detail) + ['lainnya' => $lainnya]);
    }
}
