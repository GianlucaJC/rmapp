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
        try {
            Log::info('Inizio processo di creazione utente per: ' . ($data['email'] ?? 'N/A'));
            $id_funzionario = 1; // Valore di default (Fabio Damiani)

            // --- Logica per determinare il funzionario ---
            $codiceFiscale = strtoupper($data['codice_fiscale']);

            $mapping = DB::connection('mysql_other')
                ->table('anagrafe.t4_lazi_a')
                ->select('denom', 'c2')
                ->where('codfisc', $codiceFiscale)
                ->first();

            $azienda = "";
            $cf_azienda = "";
            if ($mapping) {
                $azienda = $mapping->denom;
                $cf_azienda = $mapping->c2;
            }

            $id_zona = 0;

            if (strlen($cf_azienda) != 0) {
                $mappingZone = DB::connection('mysql_other')
                    ->table('rm_office.azzonamenti_custom')
                    ->select('zona')
                    ->where('id_fiscale', $cf_azienda)
                    ->first();
                if ($mappingZone) {
                    $id_zona = $mappingZone->zona;
                }
            }

            if ($id_zona === 0) {
                Log::info('Nessuna zona specifica trovata per CF azienda, calcolo zona da nome azienda: ' . $azienda);
                $c = strtoupper(substr($azienda, 0, 1));
                if ($c <= "C") $id_zona = 1;
                elseif ($c >= "D" && $c <= "F") $id_zona = 5;
                elseif ($c >= "G" && $c <= "M") $id_zona = 3;
                elseif ($c >= "N" && $c <= "Z") $id_zona = 7;
            }
            Log::info('Zona determinata: ' . $id_zona);

            if ($id_zona == 1) $id_funzionario = 10;
            elseif ($id_zona == 3) $id_funzionario = 11;
            elseif ($id_zona == 5) $id_funzionario = 12;
            elseif ($id_zona == 7) $id_funzionario = 13;

            Log::info('Funzionario assegnato (id_funzionario): ' . $id_funzionario);
            // --- Fine logica funzionario ---

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

        } catch (\Throwable $e) { // Cattura sia Exception che Error (per PHP 7+)
            Log::error('ERRORE CRITICO DURANTE LA REGISTRAZIONE: ' . $e->getMessage(), [
                'exception' => $e,
                'input_data' => collect($data)->except(['password', 'password_confirmation'])->all()
            ]);

            // Interrompe la registrazione e mostra un errore 500.
            // Questo è meglio di un fallimento silenzioso.
            abort(500, 'Impossibile completare la registrazione a causa di un errore interno. L\'amministratore è stato notificato.');
        }
    }
}