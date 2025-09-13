<?php

namespace App\Http\Controllers\account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $authUser = Auth::user();

        // Ambil user_id yang punya session aktif (5 menit terakhir)
        $onlineUserIds = DB::table('sessions')
            ->where('last_activity', '>=', now()->subMinutes(1)->timestamp)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique()
            ->toArray();

        // Ambil User model, kecuali user yang sedang login
        $onlineUsers = collect();
        if (!empty($onlineUserIds)) {
            $onlineUsers = User::whereIn('id', $onlineUserIds)
                ->where('id', '!=', $authUser->id)
                ->get();
        }

        // Pastikan nama variabel yang kita pass sesuai dengan yang dipakai di blade
        return view('livewire.pages.admin.dashboard', [
            'user' => $authUser,        // kalau blade pakai $user
            'onlineUsers' => $onlineUsers, // dipakai oleh loop di blade
        ]);
    }
}
