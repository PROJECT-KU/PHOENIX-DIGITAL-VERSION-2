<?php

namespace App\Models\Asthana;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Perilaku bersama isi situs Asthana.
 *
 * Gambar disimpan di disk 'public' (folder asthana/...) dan dikirim ke situs
 * sebagai alamat LENGKAP: situsnya berdiri di domain lain, jadi jalur relatif
 * seperti "/storage/x.webp" akan dicari peramban di domain situs dan 404.
 */
trait KontenSitus
{
    public static function urlGambar(?string $jalur): ?string
    {
        if (! $jalur) {
            return null;
        }

        return str_starts_with($jalur, 'http') ? $jalur : Storage::disk('public')->url($jalur);
    }

    /** Hapus berkas gambar lama; diam bila sudah tidak ada. */
    public static function hapusGambar(?string $jalur): void
    {
        if ($jalur && ! str_starts_with($jalur, 'http')) {
            Storage::disk('public')->delete($jalur);
        }
    }

    /** Daftar pendek: hanya yang ditampilkan, menurut urutan pilihan admin. */
    public function scopeTampil(Builder $q): Builder
    {
        return $q->where('tampil', true)->orderBy('urutan')->orderBy('id');
    }

    /**
     * Slug unik dari judul. Alamat yang sudah beredar tidak diganti saat
     * judulnya disunting — pemanggil hanya memakai ini untuk tulisan baru.
     */
    public static function slugUnik(string $judul, ?int $kecualiId = null): string
    {
        $dasar = Str::slug($judul) ?: 'tulisan';
        $slug = $dasar;
        $n = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($kecualiId, fn ($q) => $q->whereKeyNot($kecualiId))
            ->exists()) {
            $slug = $dasar.'-'.$n++;
        }

        return $slug;
    }
}
