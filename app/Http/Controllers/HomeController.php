<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Ketua Yayasan')) {
            return redirect()->route('ketuayayasan.dashboard');
        } elseif ($user->hasRole('Staf Yayasan')) {
            return redirect()->route('stafyayasan.dashboard');
        } elseif ($user->hasRole('Staf TU')) {
            return redirect()->route('admin.dashboard');
        }

        auth()->logout();
        return redirect()->route('login')->with('error', 'Role tidak dikenali.');
    }
}
