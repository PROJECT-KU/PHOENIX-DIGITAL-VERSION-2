<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\CustomerMessage;
use Livewire\Component;

class CustomerMessageDetail extends Component
{
    public CustomerMessage $message;
    public $status;
    public $priority;

    public function mount(CustomerMessage $message)
    {
        $this->message = $message;
        $this->status = $message->status;
        $this->priority = $message->priority;
        $this->message->markAsRead();
    }

    public function updatedStatus($value)
    {
        $this->message->update(['status' => $value]);
        $this->dispatch('toast-success', message: 'Status berhasil diperbarui!');
    }

    public function updatedPriority($value)
    {
        $this->message->update(['priority' => $value]);
        $this->dispatch('toast-success', message: 'Prioritas berhasil diperbarui!');
    }

    public function render()
    {
        return view('livewire.pages.admin.message.customer-message-detail')
            ->layout('livewire.layout.templateindex');
    }
}
