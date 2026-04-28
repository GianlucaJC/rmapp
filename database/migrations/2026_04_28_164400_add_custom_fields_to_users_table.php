<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->after('name');
            $table->string('codice_fiscale', 16)->unique()->after('last_name');
            $table->string('phone_number')->after('codice_fiscale');
            $table->string('contract_type')->after('phone_number');
            $table->string('job_title')->after('contract_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'last_name',
                'codice_fiscale',
                'phone_number',
                'contract_type',
                'job_title'
            ]);
        });
    }
};