<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\Message;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MessageList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $search = '';

    public $filterMonth = '';

    public $filterStatus = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'filterMonth', 'filterStatus']);
        $this->resetPage();
    }

    #[On('delete-message-data')]
    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_message')) {
            $this->dispatch('swal-error', message: 'Anda tidak memiliki izin menghapus pesan.');

            return;
        }

        try {
            Message::findOrFail($id)->delete();
            $this->dispatch('swal-success', message: 'Pesan berhasil dihapus.');
        } catch (\Exception $e) {
            $this->dispatch('swal-error', message: 'Gagal menghapus pesan.');
        }
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        $query = Message::latest();

        // Pencarian: nama, email, atau isi pesan
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('email', 'like', "%{$this->search}%")
                    ->orWhere('message', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterMonth) {
            $query->whereMonth('created_at', $this->filterMonth);
        }

        // Gunakan scope yang sudah ada
        if ($this->filterStatus === 'unread') {
            $query->unread();
        } elseif ($this->filterStatus === 'read') {
            $query->read();
        }

        $dataPesan = $query->paginate($this->perPage);

        // Gunakan scope unread
        $unreadCount = Message::unread()->count();

        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months->push([
                'value' => $date->format('m'),
                'label' => $date->locale('id')->isoFormat('MMMM YYYY'),
            ]);
        }

        return view('livewire.pages.admin.message.message-list', [
            'messages' => $dataPesan,
            'months' => $months,
            'unreadCount' => $unreadCount,
        ]);
    }
}
