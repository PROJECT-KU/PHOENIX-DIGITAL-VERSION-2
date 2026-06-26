<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\CustomerMessage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerMessageList extends Component
{
    use WithPagination;

    public $perPage = 10;

    public $filterMonth = '';

    public $filterStatus = '';

    public function getQueuePositionForItem($item)
    {
        return \App\Models\CustomerMessage::where('status', '!=', 'closed')
            ->whereNull('read_at')
            ->where('created_at', '<', $item->created_at)
            ->count() + 1;
    }

    public function updateStatus($id, $value)
    {
        $message = CustomerMessage::find($id);
        if ($message) {
            $message->update(['status' => $value]);
            $this->dispatch('toast-success', message: 'Status diperbarui!');
        }
    }

    public function updatePriority($id, $value)
    {
        $message = CustomerMessage::find($id);
        if ($message) {
            $message->update(['priority' => $value]);
            $this->dispatch('toast-success', message: 'Prioritas diperbarui!');
        }
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
        $this->reset(['filterMonth', 'filterStatus']);
        $this->resetPage();
    }

    public function delete($id)
    {
        if (! auth()->user()->hasPermission('delete_customer_message')) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Anda tidak memiliki izin menghapus pesan pelanggan.');

            return;
        }

        $customerMessage = CustomerMessage::find($id);

        // 1. Pastikan data ditemukan
        if (! $customerMessage) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Data Pesan Pelanggan tidak ditemukan!');
            return;
        }

        // 2. Pengecekan read_at (Jika masih null, batalkan penghapusan)
        if (is_null($customerMessage->read_at)) {
            $this->dispatch('CustomerMessage-deleteError', message: 'Pesan belum dibaca dan tidak bisa dihapus!');
            return;
        }

        // 3. Hapus file fisik jika ada
        if ($customerMessage->gambar) {
            $filePath = storage_path('app/public/img/customer-messages/' . $customerMessage->gambar);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // 4. Hapus record dari DB
        $customerMessage->delete();

        $this->dispatch('CustomerMessage-deleted', id: $id);
    }

    public function render()
    {
        $query = CustomerMessage::latest();

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
        $unreadCount = CustomerMessage::unread()->count();

        $months = collect();
        for ($i = 0; $i < 12; $i++) {
            $date = now()->subMonths($i);
            $months->push([
                'value' => $date->format('m'),
                'label' => $date->locale('id')->isoFormat('MMMM YYYY'),
            ]);
        }

        return view('livewire.pages.admin.message.customer-message-list', [
            'messages' => $dataPesan,
            'months' => $months,
            'unreadCount' => $unreadCount,
        ])
            ->layout('livewire.layout.templateindex');
    }
}
