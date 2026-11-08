<?php

namespace App\Http\Controllers\Api;

use App\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Asthana\Angka;
use App\Models\Asthana\Berita;
use App\Models\Asthana\Klien;
use App\Models\Asthana\Portofolio;
use App\Models\Asthana\Testimoni;
use App\Models\Asthana\Tim;
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
            'angka' => Angka::tampil()->get()->map(fn (Angka $a) => [
                'label' => $a->label,
                'nilai' => $a->nilai,
                'akhiran' => $a->akhiran,
                'ikon' => $a->ikon,
            ]),
            'klien' => Klien::tampil()->get()->map(fn (Klien $k) => [
                'nama' => $k->nama,
                'logo_url' => Klien::urlGambar($k->logo),
                'tautan' => $k->tautan,
            ]),
            'testimoni' => Testimoni::tampil()->get()->map(fn (Testimoni $t) => [
                'nama' => $t->nama,
                'jabatan' => $t->jabatan,
                'perusahaan' => $t->perusahaan,
                'isi' => $t->isi,
                'bintang' => $t->bintang,
                'foto_url' => Testimoni::urlGambar($t->foto),
            ]),
            'portofolio' => Portofolio::terbit()->where('unggulan', true)->urutSitus()->limit(3)->get()
                ->map(fn (Portofolio $p) => $p->keKartu()),
            'berita' => Berita::terbit()->orderByDesc('terbit_at')->limit(3)->get()
                ->map(fn (Berita $b) => $b->keKartu()),
        ]);
    }

    public function tim(): JsonResponse
    {
        return $this->ok(Tim::tampil()->get()->map(fn (Tim $t) => [
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
        $halaman = Berita::terbit()
            ->orderByDesc('terbit_at')
            ->paginate(self::PER_HALAMAN_BERITA, ['*'], 'halaman', max(1, (int) $request->query('halaman', 1)));

        return $this->ok(
            collect($halaman->items())->map(fn (Berita $b) => $b->keKartu()),
            meta: [
                'halaman' => $halaman->currentPage(),
                'halaman_terakhir' => $halaman->lastPage(),
                'total' => $halaman->total(),
            ],
        );
    }

    public function beritaDetail(string $slug): JsonResponse
    {
        $b = Berita::terbit()->where('slug', $slug)->first();

        if (! $b) {
            return $this->fail('Berita tidak ditemukan.', status: 404);
        }

        $lainnya = Berita::terbit()->whereKeyNot($b->id)->orderByDesc('terbit_at')->limit(3)->get()
            ->map(fn (Berita $x) => $x->keKartu());

        return $this->ok($b->keDetail() + ['lainnya' => $lainnya]);
    }
}
