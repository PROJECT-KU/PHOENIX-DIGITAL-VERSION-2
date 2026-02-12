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

    public $filterMonth = '';

    public $filterStatus = '';

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
        $this->reset(['filterMonth', 'filterStatus']);
        $this->resetPage();
    }

    #[On('delete-message-data')]
    public function delete($id)
    {
        try {
            Message::findOrFail($id)->delete();
            session()->flash('success', 'berhasil menghapus data lowongan');
        } catch (\Exception $e) {
            session()->flash('error', 'gagal menghapus data lowongan');
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $query = Message::latest();

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
