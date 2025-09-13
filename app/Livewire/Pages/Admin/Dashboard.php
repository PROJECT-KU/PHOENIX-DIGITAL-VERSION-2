<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Dashboard extends Component
{
    #[Layout('layouts.app')]
    public function render()
    {
        $authUser = Auth::user();

        $users = User::where('id', '!=', $authUser->id)
            ->whereNotNull('last_seen_at') // hanya user yang pernah login
            ->get()
            ->map(function ($user) {
                // status online jika last_seen_at < 5 menit
                $user->online = $user->last_seen_at->gt(now()->subMinutes(1));
                return $user;
            })
            ->sortByDesc(function ($user) {
                // urutkan: online dulu, lalu last_seen_at terbaru
                return [
                    $user->online ? 1 : 0,
                    $user->last_seen_at->timestamp,
                ];
            })
            ->take(5)
            ->values();

        return view('livewire.pages.admin.dashboard', [
            'user' => $authUser,
            'onlineUsers' => $users,
        ]);
    }
}
