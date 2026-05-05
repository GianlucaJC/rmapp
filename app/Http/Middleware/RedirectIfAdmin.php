<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the admin is logged in and tries to access a non-admin route,
        // redirect them to the admin dashboard.
        if ($request->session()->has('admin_logged_in') && !$request->is('admin*')) {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}