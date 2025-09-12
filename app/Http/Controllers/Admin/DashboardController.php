<?php

namespace App\Http\Controllers\account;

use App\Models\User;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * DashboardController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        return view('pages.admin.dashboard', compact(
            'latestUsers',
            'users',
        ));
    }
}
