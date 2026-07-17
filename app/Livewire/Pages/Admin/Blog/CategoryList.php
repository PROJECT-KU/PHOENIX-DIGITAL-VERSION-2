<?php

namespace App\Livewire\Pages\Admin\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $editingName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Dipanggil dari popup SweetAlert (input nama kategori).
     */
    public function createCategory($name): void
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
    }

    public function cancelEdit(): void
    {
        $this->reset('editingId', 'editingName');
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
        ], [], ['editingName' => 'nama kategori']);

        $old = $cat->name;
        $new = trim($data['editingName']);

        $cat->update([
            'name' => $new,
            'slug' => BlogCategory::makeSlug($new, $cat->id),
        ]);

        // Ikut memperbarui nama kategori di semua artikel yang memakainya.
        if ($old !== $new) {
            BlogPost::where('category', $old)->update(['category' => $new]);
        }

        $this->cancelEdit();
        $this->dispatch('swal-success', message: 'Kategori berhasil diperbarui.');
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
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')->paginate(10);

        return view('livewire.pages.admin.blog.category-list', [
            'categories' => $categories,
            'counts' => $counts,
        ])->layout('livewire.layout.templateindex');
    }
}
