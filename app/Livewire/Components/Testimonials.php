<?php

namespace App\Livewire\Components;

use App\Models\Testimoni;
use Livewire\Component;

class Testimonials extends Component
{
    public bool $submitted = false;

    public string $nama = '';

    public string $peran = '';

    public int $rating = 5;

    public string $pesan = '';

    protected function rules(): array
    {
        return [
            'nama' => 'required|string|min:2|max:60',
            'peran' => 'nullable|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'pesan' => 'required|string|min:10|max:500',
        ];
    }

    protected $messages = [
        'nama.required' => 'Nama wajib diisi.',
        'pesan.required' => 'Pesan testimoni wajib diisi.',
        'pesan.min' => 'Ceritakan sedikit lebih detail (min. 10 karakter).',
    ];

    public function submit(): void
    {
        $this->validate();

        Testimoni::create([
            'nama' => trim($this->nama),
            'peran' => $this->peran ? trim($this->peran) : null,
            'pesan' => trim($this->pesan),
            'rating' => $this->rating,
            'status' => 'non-active', // menunggu persetujuan admin
            'source' => 'customer',   // dikirim langsung oleh pelanggan
        ]);

        $this->reset(['nama', 'peran', 'pesan']);
        $this->rating = 5;
        $this->submitted = true;

        // Slider tidak berubah (testimoni baru non-active), tapi pastikan Swiper tetap sehat
        $this->dispatch('tm-reinit');
    }

    public function render()
    {
        $testimonials = Testimoni::where('status', 'active')
            ->latest()
            ->take(9)
            ->get();

        return view('livewire.components.testimonials', [
            'testimonials' => $testimonials,
        ]);
    }
}
