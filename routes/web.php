<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceRequestController; // Import the new controller
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\UtilityController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\HomeController; // Assicurati di importare il controller

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// !!! PER SCOPI DI SVILUPPO - DA RIMUOVERE IN PRODUZIONE !!!
// Rotta per generare hash di password.
// Accedi tramite /generate-hash/la-tua-password-qui
Route::get('/generate-hash/{password}', [UtilityController::class, 'generateHash'])
    ->where('password', '.*'); // Permette caratteri speciali nella password


// Rotta per la welcome page pubblica, non richiede login
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes...
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes...
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Password Reset Routes...
Route::get('password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email Verification Routes...
Route::get('email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Rotta per la dashboard utente dopo il login.
// La rotta /home viene esclusa dai middleware di reindirizzamento per permettere l'accesso
// anche agli amministratori loggati, che altrimenti verrebbero reindirizzati alla loro dashboard.
Route::get('/home', [HomeController::class, 'index'])->name('home')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);

// Rotta per la pagina della privacy policy
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

// Rotte per le pagine dei servizi
Route::get('/servizi', [HomeController::class, 'serviziIndex'])->name('servizi.index')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);
Route::get('/servizi/cassa-edile', [HomeController::class, 'cassaEdile'])->name('servizi.cassa-edile')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);
Route::get('/servizi/cassa-edile-latina', [HomeController::class, 'cassaEdileLatina'])->name('servizi.cassa-edile-latina')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);
Route::get('/servizi/cassa-edile-rieti', [HomeController::class, 'cassaEdileRieti'])->name('servizi.cassa-edile-rieti')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);
Route::get('/servizi/edilcassa', [HomeController::class, 'edilcassa'])->name('servizi.edilcassa')
    ->withoutMiddleware([\App\Http\Middleware\RedirectIfAuthenticated::class, \App\Http\Middleware\RedirectIfAdmin::class]);

// Rotta per la pagina dei contatti
Route::get('/contatti', [HomeController::class, 'contatti'])->name('contatti.index');

// Nuova rotta per l'invio delle richieste di servizio (richiede autenticazione)
Route::post('/servizi/send-service-request', [HomeController::class, 'sendServiceRequest'])
    ->middleware('auth')
    ->name('servizi.send-service-request');

// Nuova rotta per l'invio della busta paga
Route::post('/servizi/send-busta-paga', [HomeController::class, 'sendBustaPaga'])
    ->middleware('auth')
    ->name('servizi.send-busta-paga');

// Nuova rotta per la richiesta iniziale di analisi busta paga (senza file)
Route::post('/servizi/request-busta-paga-analysis', [HomeController::class, 'requestBustaPagaAnalysis'])
    ->middleware('auth')
    ->name('servizi.request-busta-paga-analysis');

// Rotta per il re-invio della pratica da parte del lavoratore (dopo aver caricato i documenti)
Route::post('/servizi/resubmit-request/{serviceRequest}', [HomeController::class, 'resubmitServiceRequest'])
    ->middleware('auth')->name('servizi.resubmit-request');

// Nuova rotta per l'upload di un singolo documento via AJAX
Route::post('/servizi/upload-single-document/{serviceRequest}', [HomeController::class, 'uploadSingleDocument'])
    ->middleware('auth')->name('servizi.upload-single-document');
// Nuova rotta per l'eliminazione di un singolo documento via AJAX
Route::delete('/servizi/delete-uploaded-document/{serviceRequest}', [HomeController::class, 'deleteUploadedDocument'])
    ->middleware('auth')->name('servizi.delete-uploaded-document');

// Rotte per la gestione delle iscrizioni alle notifiche push
Route::middleware('auth')->group(function () {
    Route::post('/push-subscriptions', [PushSubscriptionController::class, 'store'])->name('push.store');
    Route::post('/push-subscriptions/delete', [PushSubscriptionController::class, 'destroy'])->name('push.destroy');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.attempt');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/service-requests/data', [ServiceRequestController::class, 'getServiceRequestsData'])->name('service-requests.data');
        Route::get('/service-requests/trash', [ServiceRequestController::class, 'trash'])->name('service-requests.trash');
        Route::get('/service-requests/trash/data', [ServiceRequestController::class, 'getTrashedData'])->name('service-requests.trash.data');
        Route::put('/service-requests/trash/{id}/restore', [ServiceRequestController::class, 'restore'])->name('service-requests.restore');
        Route::delete('/service-requests/trash/{id}/force-delete', [ServiceRequestController::class, 'forceDelete'])->name('service-requests.force-delete');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::delete('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'destroy'])->name('service-requests.destroy');
        Route::put('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'update'])->name('service-requests.update'); // Nuova rotta per l'aggiornamento
        Route::post('/service-requests/{serviceRequest}/assign', [ServiceRequestController::class, 'assign'])->name('service-requests.assign'); // Rotta per riassegnare
        // Nuova rotta per il download di un documento specifico per una richiesta di servizio
        Route::get('/service-requests/{serviceRequest}/download-document/{filePath}', [ServiceRequestController::class, 'downloadDocument'])
            ->where('filePath', '.*')->name('service-requests.download-document'); // Permette slash nel filePath
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/data', [App\Http\Controllers\Admin\UserController::class, 'getUsersData'])->name('users.data');
        Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::get('/users/{user}/details-modal', [App\Http\Controllers\Admin\UserController::class, 'getUserDetails'])->name('users.details-modal');
        Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');

        // Rotte per la gestione delle News (solo superadmin)
        Route::resource('news', NewsController::class)->except(['show']);
    });
});
