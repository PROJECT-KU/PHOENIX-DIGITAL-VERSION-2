<?php

namespace App\Livewire\Pages\Admin\Presensi;

use App\Models\Presensi;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class PresensiIndex extends Component
{
    use WithPagination;

    public ?float $officeLat = null;

    public ?float $officeLng = null;

    public string $officeNama = '';

    public int $radius = 300;

    public float $minDurasiJam = 6;

    public function mount(): void
    {
        $this->loadSetting();
    }

    public function loadSetting(): void
    {
        $lat = Setting::get('presensi_lokasi_lat');
        $lng = Setting::get('presensi_lokasi_lng');
        $this->officeLat = is_numeric($lat) ? (float) $lat : null;
        $this->officeLng = is_numeric($lng) ? (float) $lng : null;
        $this->officeNama = (string) Setting::get('presensi_lokasi_nama', 'Kantor');
        $this->radius = (int) Setting::get('presensi_radius_meter', 300);
        $this->minDurasiJam = (float) Setting::get('presensi_min_durasi_jam', 6);
    }

    /** Jarak (meter) dari titik ke lokasi kantor — haversine. Null bila kantor belum diatur. */
    public function hitungJarak(float $lat, float $lng): ?int
    {
        if ($this->officeLat === null || $this->officeLng === null) {
            return null;
        }
        $r = 6371000;
        $dLat = deg2rad($this->officeLat - $lat);
        $dLng = deg2rad($this->officeLng - $lng);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat)) * cos(deg2rad($this->officeLat)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($r * $c);
    }

    public function getTodayHadirProperty(): ?Presensi
    {
        return Presensi::where('user_id', auth()->id())
            ->whereDate('tanggal', today())
            ->whereIn('tipe', ['hadir_offline', 'hadir_online'])
            ->latest('waktu_masuk')
            ->first();
    }

    public function getTodayLemburProperty(): ?Presensi
    {
        return Presensi::where('user_id', auth()->id())
            ->where('tipe', 'lembur')
            ->where('status', 'aktif')
            ->latest('waktu_masuk')
            ->first();
    }

    private function validCoords($lat, $lng): ?array
    {
        $lat = (float) $lat;
        $lng = (float) $lng;
        if (! $lat || ! $lng) {
            $this->dispatch('swal-error', message: 'Lokasi tidak terbaca. Aktifkan GPS & izinkan akses lokasi di browser.');

            return null;
        }

        return [$lat, $lng];
    }

    public function absenMasuk(string $tipe, $lat, $lng)
    {
        if (! in_array($tipe, ['hadir_offline', 'hadir_online'], true)) {
            return;
        }

        if ($this->todayHadir) {
            $this->dispatch('swal-error', message: 'Kamu sudah absen masuk hari ini.');

            return;
        }

        $coords = $this->validCoords($lat, $lng);
        if (! $coords) {
            return;
        }
        [$lat, $lng] = $coords;

        $jarak = $this->hitungJarak($lat, $lng);

        if ($tipe === 'hadir_offline') {
            if ($jarak === null) {
                $this->dispatch('swal-error', message: 'Lokasi kantor belum diatur admin. Hubungi admin untuk mengatur lokasi presensi.');

                return;
            }
            if ($jarak > $this->radius) {
                $this->dispatch('swal-error', message: 'Absen offline ditolak — kamu berada ± '.$jarak.' m dari kantor (maksimal '.$this->radius.' m).');

                return;
            }
        }

        Presensi::create([
            'user_id' => auth()->id(),
            'tanggal' => today(),
            'tipe' => $tipe,
            'waktu_masuk' => now(),
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'jarak_masuk_meter' => $jarak,
            'status' => 'aktif',
        ]);

        $label = $tipe === 'hadir_offline' ? 'Hadir Offline' : 'Hadir Online';
        $this->dispatch('swal-success', message: 'Absen masuk berhasil ('.$label.').');
    }

    public function absenLembur($lat, $lng)
    {
        if ($this->todayLembur) {
            $this->dispatch('swal-error', message: 'Masih ada sesi lembur yang belum ditutup.');

            return;
        }

        $coords = $this->validCoords($lat, $lng);
        if (! $coords) {
            return;
        }
        [$lat, $lng] = $coords;

        Presensi::create([
            'user_id' => auth()->id(),
            'tanggal' => today(),
            'tipe' => 'lembur',
            'waktu_masuk' => now(),
            'lat_masuk' => $lat,
            'lng_masuk' => $lng,
            'jarak_masuk_meter' => $this->hitungJarak($lat, $lng),
            'status' => 'aktif',
        ]);

        $this->dispatch('swal-success', message: 'Mulai lembur berhasil dicatat.');
    }

    public function absenPulang($id, $lat, $lng)
    {
        $p = Presensi::where('user_id', auth()->id())->where('status', 'aktif')->find($id);
        if (! $p) {
            $this->dispatch('swal-error', message: 'Sesi presensi aktif tidak ditemukan.');

            return;
        }

        $coords = $this->validCoords($lat, $lng);
        if (! $coords) {
            return;
        }
        [$lat, $lng] = $coords;

        $durasiMenit = $p->waktu_masuk->diffInMinutes(now());
        $isHadir = in_array($p->tipe, ['hadir_offline', 'hadir_online'], true);

        // Syarat durasi minimal hanya untuk presensi kerja (bukan lembur)
        if ($isHadir) {
            $minMenit = (int) round($this->minDurasiJam * 60);
            if ($durasiMenit < $minMenit) {
                $this->dispatch('swal-error', message: 'Belum boleh pulang. Minimal kerja '.rtrim(rtrim((string) $this->minDurasiJam, '0'), '.').' jam (baru '.intdiv($durasiMenit, 60).' jam '.($durasiMenit % 60).' menit).');

                return;
            }
        }

        $jarak = $this->hitungJarak($lat, $lng);

        // Pulang untuk hadir offline wajib dalam radius kantor
        if ($p->tipe === 'hadir_offline') {
            if ($jarak === null) {
                $this->dispatch('swal-error', message: 'Lokasi kantor belum diatur admin.');

                return;
            }
            if ($jarak > $this->radius) {
                $this->dispatch('swal-error', message: 'Pulang offline harus dalam radius kantor (kamu ± '.$jarak.' m / maksimal '.$this->radius.' m).');

                return;
            }
        }

        $p->update([
            'waktu_pulang' => now(),
            'lat_pulang' => $lat,
            'lng_pulang' => $lng,
            'jarak_pulang_meter' => $jarak,
            'durasi_menit' => $durasiMenit,
            'status' => 'selesai',
        ]);

        $this->dispatch('swal-success', message: 'Absen pulang berhasil. Durasi kerja '.intdiv($durasiMenit, 60).' jam '.($durasiMenit % 60).' menit.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $history = Presensi::where('user_id', auth()->id())
            ->latest('waktu_masuk')
            ->paginate(10);

        return view('livewire.pages.admin.presensi.presensi-index', [
            'history' => $history,
        ]);
    }
}
