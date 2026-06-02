<?php

namespace App\Livewire\Pages\Admin\Message;

use App\Models\CustomerMessage;
use Livewire\Attributes\Layout;
use Livewire\Component;

class CustomerMessageDetail extends Component
{
    public ?CustomerMessage $message;

    public function mount(CustomerMessage $message)
    {
        $this->message = $message;

        $this->message->markAsRead();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.pages.admin.message.customer-message-detail');
    }
}
