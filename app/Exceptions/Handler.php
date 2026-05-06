<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Session\TokenMismatchException; // Import the exception
use Illuminate\Support\Facades\Redirect; // Import Redirect facade

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TokenMismatchException) {
            // Determine the appropriate redirect route based on the request path
            if ($request->is('admin/*')) {
                return Redirect::route('admin.login')->withErrors([
                    'csrf_token' => 'La sessione amministrativa è scaduta o il token di sicurezza non è valido. Riprova.'
                ]);
            }

            // For regular user requests
            return Redirect::route('login')->withErrors([
                'csrf_token' => 'La sessione è scaduta o il token di sicurezza non è valido. Riprova.'
            ]);
        }

        return parent::render($request, $exception);
    }
}