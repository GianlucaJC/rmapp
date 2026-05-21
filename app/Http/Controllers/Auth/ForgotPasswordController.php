<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * This method overrides the default trait method to use a custom API for sending emails.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);

        // We will find the user first to get their email address.
        $user = User::where('email', $request->email)->first();

        // If we can't find a user by their email, we will just send back a success
        // response. This is to prevent user enumeration attacks.
        if (!$user) {
            return back()->with('status', trans(Password::RESET_LINK_SENT));
        }

        // Create a password reset token for the user.
        $token = $this->broker()->createToken($user);

        // Build the password reset link.
        $resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->getEmailForPasswordReset(),
        ]);

        // API details from HomeController.
        $apiUrl = 'https://www.filleaoffice.it:8013/auth_mail/api_send_mail.php';
        $client = new Client();
        $subject = 'Notifica di reset password';
        $expire = config('auth.passwords.users.expire');
        $appName = config('app.name', 'LazioAPP');

        // Create the HTML body for the email.
        $emailBody = "
            <h1>Notifica di reset password</h1>
            <p>Stai ricevendo questa email perché abbiamo ricevuto una richiesta di reset della password per il tuo account.</p>
            <p>Clicca sul pulsante qui sotto per resettare la tua password:</p>
            <p><a href=\"{$resetUrl}\" style=\"display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #c8102e; text-decoration: none; border-radius: 5px;\">Reset Password</a></p>
            <p>Questo link per il reset della password scadrà tra {$expire} minuti.</p>
            <p>Se non hai richiesto un reset della password, non è richiesta alcuna ulteriore azione.</p>
            <br>
            <p>Grazie,<br>Il team di {$appName}</p>
        ";

        try {
            // Encode the email body to ISO-8859-1 for compatibility with the external API.
            $encodedBody = mb_convert_encoding($emailBody, 'ISO-8859-1', 'UTF-8');

            $client->post($apiUrl, [
                'form_params' => [
                    'to' => $user->email,
                    'subject' => $subject,
                    'message' => $encodedBody,
                    'from' => "LazioAPP",
                ]
            ]);

            return back()->with('status', trans(Password::RESET_LINK_SENT));

        } catch (\Exception $e) {
            Log::error('Error sending password reset email via API: ' . $e->getMessage(), ['exception' => $e]);
            return back()->withErrors(['email' => 'Impossibile inviare il link per il reset della password. Riprova più tardi.']);
        }
    }
}