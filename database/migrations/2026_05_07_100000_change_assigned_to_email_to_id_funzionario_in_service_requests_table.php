<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aggiunge la nuova colonna per l'ID del funzionario
        Schema::table('service_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('id_funzionario')->nullable()->after('status');
        });

        // Mappa gli admin da email a ID per la migrazione dei dati
        $admins = config('admins.users', []);
        $emailToIdMap = collect($admins)->pluck('id', 'email');

        // Aggiorna i record esistenti con il nuovo ID funzionario
        if ($emailToIdMap->isNotEmpty()) {
            $requests = DB::table('service_requests')->whereNotNull('assigned_to_email')->cursor();
            foreach ($requests as $request) {
                if (isset($emailToIdMap[$request->assigned_to_email])) {
                    DB::table('service_requests')
                        ->where('id', $request->id)
                        ->update(['id_funzionario' => $emailToIdMap[$request->assigned_to_email]]);
                }
            }
        }

        // Rimuove la vecchia colonna email
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('assigned_to_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Per tornare indietro, ripristiniamo la vecchia colonna e proviamo a rimappare i dati
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('assigned_to_email')->nullable()->after('status');
        });

        $admins = config('admins.users', []);
        $idToEmailMap = collect($admins)->pluck('email', 'id');

        // Logica inversa per il rollback (opzionale ma buona pratica)
        // ...

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('id_funzionario');
        });
    }
};