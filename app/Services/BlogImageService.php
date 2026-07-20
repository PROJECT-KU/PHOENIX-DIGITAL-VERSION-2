<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Kompresi gambar blog (cover & gambar di isi artikel) ke WEBP.
 * Hanya menurunkan resolusi (tidak pernah upscale) + resampling kualitas tinggi
 * supaya hasil tetap tajam / tidak blur.
 */
class BlogImageService
{
    public int $coverMaxWidth = 1600;

    public int $bodyMaxWidth = 1400;

    public int $quality = 82;

    /** Ukuran wajar cover yang dianggap sudah "ringan" (KB). */
    public int $coverIdealKB = 350;

    public function available(): bool
    {
        return function_exists('imagewebp');
    }

    /**
     * Kompres file gambar (mis. dari upload) ke WEBP di $dest.
     */
    public function compressFileToWebp(string $src, string $dest, int $maxWidth, ?int $quality = null): bool
    {
        if (! $this->available()) {
            return false;
        }

        $info = @getimagesize($src);
        if (! $info) {
            return false;
        }

        @ini_set('memory_limit', '256M');
        $mime = $info['mime'] ?? '';

        switch ($mime) {
            case 'image/jpeg': $img = @imagecreatefromjpeg($src);
                break;
            case 'image/png': $img = @imagecreatefrompng($src);
                break;
            case 'image/webp': $img = @imagecreatefromwebp($src);
                break;
            default: return false;
        }
        if (! $img) {
            return false;
        }

        // Perbaiki orientasi (foto dari HP) agar tidak miring.
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($src);
            $o = $exif['Orientation'] ?? 0;
            if ($o === 3) {
                $img = imagerotate($img, 180, 0);
            } elseif ($o === 6) {
                $img = imagerotate($img, -90, 0);
            } elseif ($o === 8) {
                $img = imagerotate($img, 90, 0);
            }
        }

        return $this->resampleToWebp($img, $dest, $maxWidth, $quality ?? $this->quality);
    }

    /**
     * Resample sebuah GD image ke WEBP (skala TURUN saja) lalu bebaskan memorinya.
     */
    public function resampleToWebp($img, string $dest, int $maxWidth, ?int $quality = null): bool
    {
        if (! $img) {
            return false;
        }

        @ini_set('memory_limit', '256M');
        $w = imagesx($img);
        $h = imagesy($img);
        $nw = $w;
        $nh = $h;
        if ($w > $maxWidth) {
            $nw = $maxWidth;
            $nh = (int) round($h * ($maxWidth / $w));
        }

        $canvas = imagecreatetruecolor($nw, $nh);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = @imagewebp($canvas, $dest, $quality ?? $this->quality);

        imagedestroy($img);
        imagedestroy($canvas);

        return $ok && file_exists($dest);
    }

    /**
     * Cari gambar base64 yang tertanam di isi artikel, simpan sebagai file WEBP
     * ringan, lalu ganti src-nya + tambahkan lazy-load.
     */
    public function processBodyImages(string $html): string
    {
        if (trim($html) === '' || ! $this->available()) {
            return $html;
        }

        return preg_replace_callback(
            '/<img\b[^>]*\bsrc=("|\')(data:image\/(?:png|jpe?g|webp);base64,[^"\']+)\1[^>]*>/i',
            function ($m) {
                $url = $this->saveBase64Image($m[2]);
                if (! $url) {
                    return $m[0];
                }

                $img = preg_replace('/\bsrc=("|\')data:image[^"\']+\1/i', 'src="'.$url.'"', $m[0]);
                if (stripos($img, 'loading=') === false) {
                    $img = preg_replace('/<img\b/i', '<img loading="lazy" decoding="async"', $img, 1);
                }

                return $img;
            },
            $html
        ) ?? $html;
    }

    /**
     * Apakah body masih mengandung gambar base64 (perlu diproses)?
     */
    public function bodyHasBase64(string $html): bool
    {
        return stripos($html, 'base64,') !== false;
    }

    /**
     * Apakah cover tersimpan masih perlu dikompres ulang?
     */
    public function coverNeedsRecompress(?string $filename): bool
    {
        [$abs, $info] = $this->coverInfo($filename);
        if (! $info) {
            return false;
        }

        $isWebp = ($info['mime'] ?? '') === 'image/webp';
        $w = $info[0] ?? 0;
        $kb = @filesize($abs) / 1024;

        return ! ($isWebp && $w <= $this->coverMaxWidth && $kb < $this->coverIdealKB);
    }

    /**
     * Kompres ulang cover yang sudah tersimpan (di img/blog/).
     * Return nama file BARU bila diproses, nama lama bila tidak perlu, atau null bila hilang.
     */
    public function recompressStoredCover(?string $filename): ?string
    {
        if (! $filename) {
            return null;
        }

        if (! $this->coverNeedsRecompress($filename)) {
            return $filename;
        }

        $disk = Storage::disk('public');
        $abs = $disk->path('img/blog/'.$filename);
        $newName = 'blog_'.time().'_'.mt_rand(10000, 99999).'.webp';
        $newAbs = $disk->path('img/blog/'.$newName);

        if (! $this->compressFileToWebp($abs, $newAbs, $this->coverMaxWidth)) {
            return $filename;
        }

        @unlink($abs);

        return $newName;
    }

    private function coverInfo(?string $filename): array
    {
        if (! $filename) {
            return [null, null];
        }

        $disk = Storage::disk('public');
        $rel = 'img/blog/'.$filename;
        if (! $disk->exists($rel)) {
            return [null, null];
        }

        $abs = $disk->path($rel);
        $info = @getimagesize($abs);

        return [$abs, $info ?: null];
    }

    private function saveBase64Image(string $dataUri): ?string
    {
        if (! preg_match('/^data:image\/(?:png|jpe?g|webp);base64,(.+)$/i', $dataUri, $mm)) {
            return null;
        }

        $data = base64_decode($mm[1], true);
        if ($data === false) {
            return null;
        }

        $img = @imagecreatefromstring($data);
        if (! $img) {
            return null;
        }

        $dir = Storage::disk('public')->path('img/blog/content');
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filename = 'content_'.time().'_'.mt_rand(10000, 99999).'.webp';
        if (! $this->resampleToWebp($img, $dir.DIRECTORY_SEPARATOR.$filename, $this->bodyMaxWidth)) {
            return null;
        }

        // Path relatif (/storage/...) supaya portabel antar domain.
        //
        // Dibangun langsung, TIDAK lewat Storage::url() lalu parse_url: URL ini
        // ikut tersimpan permanen di dalam body artikel, jadi tidak boleh
        // bergantung pada APP_URL. Saat APP_URL berakhiran "/", cara lama
        // menghasilkan "//storage/..." yang dibaca browser sebagai nama host
        // dan membuat gambar gagal tampil selamanya.
        return '/storage/img/blog/content/'.$filename;
    }
}
