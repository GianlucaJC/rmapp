<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Show the admin login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an admin login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        Log::info('Tentativo di login admin per email: ' . $credentials['email']);

        $staticAdmins = config('admins.users', []);
        Log::info('Utenti admin caricati dal file di configurazione:', ['count' => count($staticAdmins)]);
        $loggedInUser = null;

        foreach ($staticAdmins as $admin) {
            Log::info('Controllo utente admin dal file di configurazione: ' . $admin['email']);

            $passwordInConfig = $admin['password'];
            $passwordMatches = Hash::check($credentials['password'], $passwordInConfig);
            
            Log::info("Risultato controllo per {$admin['email']}: [Email Match: " . ($credentials['email'] === $admin['email'] ? 'sì' : 'no') . "] - [Password Match: " . ($passwordMatches ? 'sì' : 'no') . "]");

            if ($credentials['email'] === $admin['email'] && $passwordMatches) {
                $loggedInUser = $admin;
                Log::info('Login Riuscito. Utente corrispondente trovato: ' . $admin['email']);
                break;
            }
        }

        if ($loggedInUser) {
            Log::info('Creazione sessione per: ' . $loggedInUser['email']);
            $request->session()->put('admin_logged_in', true);
            $request->session()->put('admin_user', [
                'id' => $loggedInUser['id'],
                'email' => $loggedInUser['email'],
                'superadmin' => $loggedInUser['superadmin'],
                'name' => $loggedInUser['name'],
            ]);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        Log::warning('Login Fallito. Nessun utente admin corrispondente trovato o password errata per email: ' . $credentials['email']);
        return back()->withErrors([
            'email' => 'Le credenziali fornite non sono corrette.',
        ])->onlyInput('email');
    }

    /**
     * Log the admin out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        $request->session()->forget('admin_logged_in');
        $request->session()->forget('admin_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
