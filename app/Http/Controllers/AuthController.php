<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // form login
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('agents.index'); // atau dashboard nanti
        }

        return view('auth.login');
    }

    // proses login
    
public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (!Auth::attempt($credentials, $request->boolean('remember'))) {
        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->withInput();
    }

    $request->session()->regenerate();

    $role = strtolower(trim((string) auth()->user()->role));

    return match ($role) {
        'admin' => redirect()->route('admin.dashboard'),
        'coa'   => redirect()->route('coa.dashboard'),
        'rm', 'bdp' => redirect()->route('user.dashboard'),
        default => redirect()->route('dashboard'), // fallback: route "/" yang auto-redirect
    };
}

    // logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
