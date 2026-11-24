<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }
    public function login(Request $request) : RedirectResponse
    {
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'username' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email ?? '-',
                'aktivitas' => 'Masuk',
                'akses' => $user->hasRole(['Ketua Yayasan', 'Staf Yayasan']) ? 'ALL' : ($user->jenjang->nama_jenjang ?? 'ALL'),
            ]);

            if ($user->hasRole('Ketua Yayasan')) {
                return redirect()->route('ketuayayasan.dashboard');
            } elseif ($user->hasRole('Staf Yayasan')) {
                return redirect()->route('stafyayasan.dashboard');
            } elseif ($user->hasRole('Staf TU')) {
                return redirect()->route('admin.dashboard');
            }

            // Role tidak dikenali — logout dan kembalikan ke login
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak memiliki akses ke sistem.');
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email ?? '-',
                'aktivitas' => 'Keluar',
                'akses' => $user->hasRole(['Ketua Yayasan', 'Staf Yayasan']) ? 'ALL' : ($user->jenjang->nama_jenjang ?? 'ALL'),
            ]);
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

}