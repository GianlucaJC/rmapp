<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'codice_fiscale' => ['required', 'string', 'size:16', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:20'],
            'contract_type' => ['required', 'string', 'max:255'],
            'job_title' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'privacy' => ['accepted'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $id_funzionario = 1; // Valore di default (Fabio Damiani)

        try {
            // Esempio di query sulla seconda connessione DB per trovare il funzionario.
            // Adatta 'mysql_other', 'mappatura_utenti_funzionari', 'cf_utente' e 'id_funzionario_ref'
            // in base alla tua configurazione e struttura del DB.
            $codiceFiscale = strtoupper($data['codice_fiscale']);

            $mapping = DB::connection('mysql_other')
                ->table('mappatura_utenti_funzionari')
                ->where('cf_utente', $codiceFiscale)
                ->first();

            if ($mapping) {
                $id_funzionario = $mapping->id_funzionario_ref;
            }

        } catch (\Exception $e) {
            // È una buona pratica loggare l'errore se la connessione al secondo DB fallisce,
            // senza però bloccare la registrazione dell'utente.
            Log::error('Errore durante la connessione al DB secondario per assegnazione funzionario: ' . $e->getMessage());
            // La registrazione procederà con id_funzionario = null.
        }

        return User::create([
            'name' => $data['name'],
            'last_name' => $data['last_name'],
            'codice_fiscale' => strtoupper($data['codice_fiscale']),
            'phone_number' => $data['phone_number'],
            'contract_type' => $data['contract_type'],
            'job_title' => $data['job_title'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'id_funzionario' => $id_funzionario,
        ]);
    }
}