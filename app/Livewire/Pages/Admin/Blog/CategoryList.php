<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingDescription = '';

    /** Nama kategori tujuan saat menggabungkan. */
    public string $gabungKe = '';

    /** nama | jumlah */
    public string $urut = 'nama';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedUrut(): void
    {
        if (! in_array($this->urut, ['nama', 'jumlah'], true)) {
            $this->urut = 'nama';
        }

        $this->resetPage();
    }

    /**
     * Dipanggil dari popup SweetAlert (input nama kategori).
     */
    public function createCategory($name, $description = null): void
    {
        if (! auth()->user()->hasPermission('create_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menambah kategori.');

            return;
        }

        $name = trim((string) $name);
        $validator = Validator::make(
            ['name' => $name],
            ['name' => 'required|string|min:2|max:60|unique:blog_categories,name'],
            [],
            ['name' => 'nama kategori']
        );

        if ($validator->fails()) {
            $this->dispatch('swal-error', message: $validator->errors()->first());

            return;
        }

        BlogCategory::create([
            'name' => $name,
            'slug' => BlogCategory::makeSlug($name),
            'description' => filled($description) ? Str::limit(trim((string) $description), 255, '') : null,
        ]);

        $this->dispatch('swal-success', message: 'Kategori berhasil ditambahkan.');
    }

    public function startEdit(int $id): void
    {
        $cat = BlogCategory::find($id);
        if (! $cat) {
            return;
        }

        $this->editingId = $cat->id;
        $this->editingName = $cat->name;
        $this->editingDescription = (string) $cat->description;
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingName', 'editingDescription', 'gabungKe');
    }

    public function saveEdit(): void
    {
        if (! auth()->user()->hasPermission('edit_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah kategori.');

            return;
        }

        $cat = BlogCategory::find($this->editingId);
        if (! $cat) {
            $this->cancelEdit();

            return;
        }

        $data = $this->validate([
            'editingName' => 'required|string|min:2|max:60|unique:blog_categories,name,'.$cat->id,
            'editingDescription' => 'nullable|string|max:255',
        ], [], ['editingName' => 'nama kategori', 'editingDescription' => 'deskripsi kategori']);

        $old = $cat->name;
        $new = trim($data['editingName']);

        $cat->update([
            'name' => $new,
            'slug' => BlogCategory::makeSlug($new, $cat->id),
            'description' => filled($data['editingDescription'] ?? null) ? trim($data['editingDescription']) : null,
        ]);

        // Ikut memperbarui nama kategori di semua artikel yang memakainya.
        if ($old !== $new) {
            BlogPost::where('category', $old)->update(['category' => $new]);
        }

        $this->cancelEdit();
        $this->dispatch('swal-success', message: 'Kategori berhasil diperbarui.');
    }

    /**
     * Gabungkan satu kategori ke kategori lain.
     *
     * Semua artikel dipindah lebih dulu, baru kategori asalnya dihapus —
     * urutan sebaliknya meninggalkan artikel tanpa kategori bila prosesnya
     * gagal di tengah.
     */
    public function gabungkan(int $dariId, string $keNama): void
    {
        if (! auth()->user()->hasPermission('edit_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin mengubah kategori.');

            return;
        }

        $dari = BlogCategory::find($dariId);
        $ke = BlogCategory::where('name', trim($keNama))->first();

        if (! $dari || ! $ke || $dari->id === $ke->id) {
            $this->dispatch('swal-error', message: 'Kategori tujuan tidak ditemukan.');

            return;
        }

        $jumlah = BlogPost::withTrashed()->where('category', $dari->name)->update(['category' => $ke->name]);
        $dari->delete();
        $this->cancelEdit();

        $this->dispatch('swal-success', message: $jumlah.' artikel dipindah ke "'.$ke->name.'", kategori "'.$dari->name.'" dihapus.');
    }

    /** Gabungkan kategori yang sedang disunting ke kategori yang dipilih. */
    public function gabungkanTerpilih(): void
    {
        if (! $this->editingId || $this->gabungKe === '') {
            return;
        }

        $this->gabungkan($this->editingId, $this->gabungKe);
    }

    /** Nama kategori lain — untuk pilihan tujuan penggabungan. */
    public function kategoriLain(int $kecualiId): array
    {
        return BlogCategory::where('id', '!=', $kecualiId)->orderBy('name')->pluck('name')->all();
    }

    public function delete(int $id): void
    {
        if (! auth()->user()->hasPermission('delete_blog')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus kategori.');

            return;
        }

        $cat = BlogCategory::find($id);
        if (! $cat) {
            return;
        }

        // Blokir hapus kalau kategori masih dipakai artikel (cegah artikel jadi "yatim").
        $used = BlogPost::where('category', $cat->name)->count();
        if ($used > 0) {
            $this->dispatch('swal-error', message: 'Kategori "'.$cat->name.'" masih dipakai '.$used.' artikel. Ubah/kosongkan kategori artikel tersebut dulu sebelum menghapus.');

            return;
        }

        $cat->delete();
        $this->dispatch('swal-success', message: 'Kategori berhasil dihapus.');
    }

    public function render()
    {
        $counts = BlogPost::query()
            ->whereNotNull('category')->where('category', '!=', '')
            ->selectRaw('category, COUNT(*) as c')
            ->groupBy('category')
            ->pluck('c', 'category');

        $categories = BlogCategory::query()
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(12);

        // Urut menurut jumlah artikel dilakukan sesudah halaman diambil:
        // jumlahnya berasal dari tabel artikel, bukan kolom di kategori.
        if ($this->urut === 'jumlah') {
            $categories->setCollection(
                $categories->getCollection()
                    ->sortByDesc(fn ($c) => (int) ($counts[$c->name] ?? 0))
                    ->values()
            );
        }

        return view('livewire.pages.admin.blog.category-list', [
            'categories' => $categories,
            'counts' => $counts,
        ])->layout('livewire.layout.templateindex');
    }
}
