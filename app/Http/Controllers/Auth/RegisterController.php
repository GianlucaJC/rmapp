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
        /*
            - cercare prima l'azienda (dedotta dal nominativo) nel db rm_office/azzonamenti_custom:
            - se c'è deduco la zona e prendo il primo utente di zona dal db frt/utenti_zona
            - se non c'è prendo la zona tramite la regola base (vedi helper in c:\wamp\www\roma\model\helper.php) alla function select_zone()
            (in base al primo carattere dell'azienda)
        */

            $codiceFiscale = strtoupper($data['codice_fiscale']);

            $mapping = DB::connection('mysql_other')
                ->table('anagrafe.t4_lazi_a')
                ->select('denom','c2')
                ->where('codfisc', $codiceFiscale)
                ->first();
            
            $azienda="";$cf_azienda="";
            if ($mapping) {
                //$id_funzionario = $mapping->id_funzionario_ref;
                $azienda = $mapping->denom;
                $cf_azienda = $mapping->c2;
            }

            $id_zona=0;

            if (strlen($cf_azienda)!=0) {
                $mapping = DB::connection('mysql_other')
                    ->table('rm_office.azzonamenti_custom')
                    ->select('zona')
                    ->where('id_fiscale', $cf_azienda)
                    ->first();
                if ($mapping) {
                    $id_zona = $mapping->zona;
                }
                            
            } 
            if (isnull($zona) || $id_zona==0 || strlen($zona)==0) {
                $c=strtoupper(substr($azienda,0,1));
                if ($c<="C") $id_zona=1;
                if ($c>="D" && $c<="F") $id_zona=5;
                if ($c>="G" && $c<="M") $id_zona=3;
                if ($c>="N" && $c<="Z") $id_zona=7;
            }

            //id_funzionario da array statico admin da config/admins.php
            $id_funzionario=1; //Fabio
            if ($id_zona==1) $id_funzionario=10;
            if ($id_zona==3) $id_funzionario=11;
            if ($id_zona==5) $id_funzionario=12;
            if ($id_zona==7) $id_funzionario=13;
            
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