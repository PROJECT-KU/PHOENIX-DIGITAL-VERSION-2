<?php

namespace App\Livewire\Pages\Admin\Orcha\Blog;

use App\Livewire\Pages\Admin\Orcha\Concerns\MemanggilOrcha;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Daftar artikel blog Orcha di lemon.
 *
 * Datanya dari API Orcha, bukan tabel blog_posts milik Phoenix — dua blog yang
 * berbeda, di basis data yang berbeda, dan sengaja tidak pernah dicampur.
 * Menu ini hanya muncul di mode Orcha, jadi admin tidak pernah melihat dua
 * daftar blog sekaligus.
 */
#[Layout('livewire.layout.templateindex')]
#[Title('Blog Orcha')]
class OrchaBlogList extends Component
{
    use MemanggilOrcha;

    #[Url(as: 'kategori', except: '')]
    public string $filterKategori = '';

    public function updatedFilterKategori(): void
    {
        $this->halaman = 1;
    }

    public function bersihkanSaringan(): void
    {
        $this->filterKategori = '';

        // Menimpa milik trait, lalu memanggilnya kembali supaya saringan
        // bersama (cari + status + halaman) tetap satu tempat pengaturannya.
        $this->cari = '';
        $this->filterStatus = '';
        $this->halaman = 1;
    }

    public function adaSaringan(): bool
    {
        return $this->cari !== '' || $this->filterStatus !== '' || $this->filterKategori !== '';
    }

    public function hapus(int $id): void
    {
        if ($this->hapusData("/artikel/$id", 'Artikel dihapus.')) {
            // Halaman terakhir bisa jadi kosong setelah barisnya habis dihapus;
            // tanpa ini admin melihat daftar kosong dan mengira semuanya hilang.
            $this->halaman = max(1, $this->halaman);
        }
    }

    public function render()
    {
        $balasan = $this->muat('/artikel', $this->parameterDaftar([
            'kategori' => $this->filterKategori,
        ]));

        return view('livewire.pages.admin.orcha.blog.orcha-blog-list', [
            'artikel' => $balasan['data'] ?? [],
            'meta' => $balasan['meta'] ?? ['halaman' => 1, 'halaman_terakhir' => 1, 'total' => 0],
            'daftarKategori' => $this->rujukan('kategori_artikel'),
        ]);
    }
}
