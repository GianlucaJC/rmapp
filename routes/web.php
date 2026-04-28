<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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



// Rotta per la dashboard utente dopo il login.
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Rotte per le pagine dei servizi
Route::get('/servizi', [HomeController::class, 'serviziIndex'])->name('servizi.index');
Route::get('/servizi/cassa-edile', [HomeController::class, 'cassaEdile'])->name('servizi.cassa-edile');
Route::get('/servizi/edilcassa', [HomeController::class, 'edilcassa'])->name('servizi.edilcassa');
