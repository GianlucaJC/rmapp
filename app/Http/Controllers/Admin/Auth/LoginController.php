<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
            if ($credentials['email'] === $admin['email'] && $credentials['password'] === $admin['password']) {
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
