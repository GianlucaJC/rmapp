<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UtilityController extends Controller
{
    /**
     * Genera un hash bcrypt per una data password.
     * SOLO PER USO DI SVILUPPO/CONFIGURAZIONE. RIMUOVERE IN PRODUZIONE.
     *
     * @param  string  $password
     * @return \Illuminate\Http\Response
     */
    public function generateHash(string $password)
    {
        // Utilizza la configurazione di default da config/hashing.php
        $hash = Hash::make($password);

        return response(
            "<h3>Generatore Hash Password</h3>" .
            "<p><b>Password:</b> " . htmlspecialchars($password) . "</p>" .
            "<p><b>Hash Bcrypt:</b> <code>" . htmlspecialchars($hash) . "</code></p>" .
            "<hr><p><em>Copia la stringa dell'hash e incollala nel file <code>config/admins.php</code></em></p>"
        );
    }
}