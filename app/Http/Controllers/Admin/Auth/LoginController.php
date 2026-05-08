<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        $staticAdmins = config('admins.users', []);
        $loggedInUser = null;
        foreach ($staticAdmins as $admin) {
            $passwordInConfig = $admin['password'];

            // Check if the password from config is a valid hash. If not, perform a simple string comparison.
            // This allows the superadmin to log in with the plain-text password from the config.
            $passwordMatches = Hash::info($passwordInConfig)['algoName'] !== 'unknown'
                ? Hash::check($credentials['password'], $passwordInConfig)
                : $credentials['password'] === $passwordInConfig;

            if ($credentials['email'] === $admin['email'] && $passwordMatches) {
                $loggedInUser = $admin;
                break;
            }
        }

        if ($loggedInUser) {
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
