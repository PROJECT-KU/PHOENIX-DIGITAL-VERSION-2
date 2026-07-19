<?php

namespace App\Livewire\Pages\Admin\Presensi;

use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PresensiSetting extends Component
{
    public string $lokasiNama = '';

    public $lat = '';

    public $lng = '';

    public $radius = 300;

    public $minDurasiJam = 6;

    public function mount(): void
    {
        $this->lokasiNama = (string) Setting::get('presensi_lokasi_nama', 'Kantor Pusat');
        $this->lat = Setting::get('presensi_lokasi_lat', '');
        $this->lng = Setting::get('presensi_lokasi_lng', '');
        $this->radius = (int) Setting::get('presensi_radius_meter', 300);
        $this->minDurasiJam = (float) Setting::get('presensi_min_durasi_jam', 6);
    }

    /** Dipanggil dari tombol "Gunakan Lokasi Saya" (JS geolocation). */
    public function pakaiLokasiSaya($lat, $lng): void
    {
        $this->lat = round((float) $lat, 7);
        $this->lng = round((float) $lng, 7);
        $this->dispatch('swal-success', message: 'Lokasi saat ini dipakai. Jangan lupa simpan.');
    }

    public function save(): void
    {
        $this->validate([
            'lokasiNama' => 'required|string|max:100',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:10|max:5000',
            'minDurasiJam' => 'required|numeric|min:0|max:24',
        ], [], [
            'lokasiNama' => 'nama lokasi',
            'lat' => 'latitude',
            'lng' => 'longitude',
            'radius' => 'radius',
            'minDurasiJam' => 'durasi minimal',
        ]);

        Setting::set('presensi_lokasi_nama', $this->lokasiNama);
        Setting::set('presensi_lokasi_lat', (string) $this->lat);
        Setting::set('presensi_lokasi_lng', (string) $this->lng);
        Setting::set('presensi_radius_meter', (string) (int) $this->radius);
        Setting::set('presensi_min_durasi_jam', (string) (float) $this->minDurasiJam);

        $this->dispatch('swal-success', message: 'Pengaturan presensi berhasil disimpan.');
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.presensi.presensi-setting');
    }
}
