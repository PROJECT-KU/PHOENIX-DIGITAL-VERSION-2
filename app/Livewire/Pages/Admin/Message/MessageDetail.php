<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\Message;
use Livewire\Attributes\Layout;
use Livewire\Component;

class MessageDetail extends Component
{
    public ?Message $message;

    public function mount(Message $message)
    {
        $this->message = $message;

        $this->message->markAsRead();
    }

    #[Layout('livewire.layout.templateindex')]
    public function render()
    {
        return view('livewire.pages.admin.message.message-detail');
    }
}
