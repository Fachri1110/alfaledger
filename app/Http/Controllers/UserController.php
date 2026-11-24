<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Jenjang;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Helper to get dashboard route name depending on current user role
     */
    private function getDashboardRouteName()
    {
        $user = auth()->user();
        if ($user?->hasRole('Ketua Yayasan')) {
            return 'ketuayayasan.dashboard';
        }
        if ($user?->hasRole('Staf Yayasan')) {
            return 'stafyayasan.dashboard';
        }
        return 'superadmin.dashboard';
    }

    /**
     * Display Super Admin / Yayasan Dashboard with tab data
     */
    public function dashboard()
    {
        $users = User::with('jenjang')->get();
        $jenjangs = Jenjang::all();
        $activityLogs = \App\Models\ActivityLog::latest()->take(10)->get();
        return view('superadmin.dashboard', compact('users', 'jenjangs', 'activityLogs'));
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route($this->getDashboardRouteName());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route($this->getDashboardRouteName());
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return redirect()->route($this->getDashboardRouteName());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return redirect()->route($this->getDashboardRouteName());
    }

    /**
     * Store a newly created staff (user) in storage.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        if (!$request->has('name') && $request->has('username')) {
            $request->merge(['name' => $request->username]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:16|unique:users',
            'email' => 'nullable|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'jenjang_id' => 'nullable|exists:jenjangs,id',
            'role' => 'required|string|in:Ketua Yayasan,Staf Yayasan,Staf TU',
        ]);

        if ($currentUser->hasRole('Staf Yayasan') && $request->role !== 'Staf TU') {
            return redirect()->route($this->getDashboardRouteName())->with('error', 'Staf Yayasan hanya dapat menambahkan akun Staf TU.');
        }

        $isYayasanRole = in_array($request->role, ['Ketua Yayasan', 'Staf Yayasan']);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'jenjang_id' => $isYayasanRole ? null : $request->jenjang_id,
        ]);

        $user->assignRole($request->role);

        return redirect()->route($this->getDashboardRouteName())->with('success', 'Staf baru berhasil ditambahkan.');
    }

    /**
     * Update the specified staff in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();

        if (!$request->has('name') && $request->has('username')) {
            $request->merge(['name' => $request->username]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:16|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'jenjang_id' => 'nullable|exists:jenjangs,id',
            'role' => 'required|string|in:Ketua Yayasan,Staf Yayasan,Staf TU',
        ]);

        if ($currentUser->hasRole('Staf Yayasan')) {
            if ($user->hasRole('Ketua Yayasan')) {
                return redirect()->route($this->getDashboardRouteName())->with('error', 'Staf Yayasan tidak dapat mengedit akun Ketua Yayasan.');
            }
            if ($user->hasRole('Staf Yayasan') && $user->id !== $currentUser->id) {
                return redirect()->route($this->getDashboardRouteName())->with('error', 'Staf Yayasan tidak dapat mengedit akun Staf Yayasan lain.');
            }
            if ($user->id === $currentUser->id) {
                $request->merge(['role' => 'Staf Yayasan']);
            } elseif ($request->role !== 'Staf TU') {
                return redirect()->route($this->getDashboardRouteName())->with('error', 'Staf Yayasan hanya dapat mengedit akun Staf TU.');
            }
        }

        $isYayasanRole = in_array($request->role, ['Ketua Yayasan', 'Staf Yayasan']);

        $user->name = $request->name;
        $user->username = $request->username;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->jenjang_id = $isYayasanRole ? null : $request->jenjang_id;
        $user->save();

        $user->syncRoles([$request->role]);

        return redirect()->route($this->getDashboardRouteName())->with('success', 'Data staf berhasil diperbarui.');
    }

    /**
     * Remove the specified staff from storage.
     */
    public function destroy(User $user)
    {
        $currentUser = auth()->user();

        if ($currentUser->hasRole('Staf Yayasan')) {
            return redirect()->route($this->getDashboardRouteName())->with('error', 'Staf Yayasan tidak memiliki hak akses untuk menghapus akun staf.');
        }

        if ($user->id === $currentUser->id) {
            return redirect()->route($this->getDashboardRouteName())->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return redirect()->route($this->getDashboardRouteName())->with('success', 'Staf berhasil dihapus.');
    }
}
