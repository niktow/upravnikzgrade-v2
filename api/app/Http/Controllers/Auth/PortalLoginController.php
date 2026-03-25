<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortalLoginController extends Controller
{
    /**
     * Prikaži login formu
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole();
        }

        return view('portal.auth.login');
    }

    /**
     * Obradi login zahtev
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return $this->redirectBasedOnRole();
        }

        return back()->withErrors([
            'email' => 'Pogrešni pristupni podaci.',
        ])->onlyInput('email');
    }

    /**
     * Odjava
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Preusmeri korisnika na osnovu uloge
     */
    protected function redirectBasedOnRole()
    {
        $user = Auth::user();

        if ($user->isTenant()) {
            return redirect()->route('portal.dashboard');
        }

        // Upravnici/admini idu na admin panel
        return redirect('/admin');
    }
}
