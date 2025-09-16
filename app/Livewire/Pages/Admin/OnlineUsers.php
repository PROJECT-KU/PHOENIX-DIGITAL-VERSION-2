<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class OnlineUsers extends Component
{
    public function render()
    {
        $users = User::where('id', '!=', Auth::id())
            ->orderByRaw('CASE WHEN last_seen_at >= ? THEN 0 ELSE 1 END', [now()->subSeconds(10)]) // online dulu
            ->orderBy('last_seen_at', 'desc') // dalam tiap group, urut terbaru
            ->limit(5) // tampilkan 5 user saja
            ->get();

        return view('livewire.pages.admin.online-users', compact('users'));
    }
}
