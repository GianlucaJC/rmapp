<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceRequestController; // Import the new controller
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

// Rotta per la welcome page pubblica, non richiede login
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

// Rotta per la dashboard utente dopo il login.
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Rotta per la pagina della privacy policy
Route::view('/privacy-policy', 'privacy-policy')->name('privacy.policy');

// Rotte per le pagine dei servizi
Route::get('/servizi', [HomeController::class, 'serviziIndex'])->name('servizi.index');
Route::get('/servizi/cassa-edile', [HomeController::class, 'cassaEdile'])->name('servizi.cassa-edile');
Route::get('/servizi/edilcassa', [HomeController::class, 'edilcassa'])->name('servizi.edilcassa');

// Nuova rotta per l'invio delle richieste di servizio (richiede autenticazione)
Route::post('/servizi/send-service-request', [HomeController::class, 'sendServiceRequest'])
    ->middleware('auth')
    ->name('servizi.send-service-request');

// Rotta per il re-invio della pratica da parte del lavoratore (dopo aver caricato i documenti)
Route::post('/servizi/resubmit-request/{serviceRequest}', [HomeController::class, 'resubmitServiceRequest'])
    ->middleware('auth')->name('servizi.resubmit-request');

// Nuova rotta per l'upload di un singolo documento via AJAX
Route::post('/servizi/upload-single-document/{serviceRequest}', [HomeController::class, 'uploadSingleDocument'])
    ->middleware('auth')->name('servizi.upload-single-document');
// Nuova rotta per l'eliminazione di un singolo documento via AJAX
Route::delete('/servizi/delete-uploaded-document/{serviceRequest}', [HomeController::class, 'deleteUploadedDocument'])
    ->middleware('auth')->name('servizi.delete-uploaded-document');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.attempt');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/service-requests/data', [ServiceRequestController::class, 'getServiceRequestsData'])->name('service-requests.data');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
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
    });
});
