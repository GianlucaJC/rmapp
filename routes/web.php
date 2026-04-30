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

// Rotte per le pagine dei servizi
Route::get('/servizi', [HomeController::class, 'serviziIndex'])->name('servizi.index');
Route::get('/servizi/cassa-edile', [HomeController::class, 'cassaEdile'])->name('servizi.cassa-edile');
Route::get('/servizi/edilcassa', [HomeController::class, 'edilcassa'])->name('servizi.edilcassa');

// Nuova rotta per l'invio delle richieste di servizio (richiede autenticazione)
Route::post('/servizi/send-service-request', [HomeController::class, 'sendServiceRequest'])
    ->middleware('auth')
    ->name('servizi.send-service-request');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.attempt');

    Route::middleware('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/service-requests/data', [ServiceRequestController::class, 'getServiceRequestsData'])->name('service-requests.data');
        Route::get('/service-requests/{serviceRequest}', [ServiceRequestController::class, 'show'])->name('service-requests.show');
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
    });
});
