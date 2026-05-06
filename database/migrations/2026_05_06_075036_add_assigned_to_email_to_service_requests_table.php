<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // In database/migrations/xxxx_xx_xx_xxxxxx_add_assigned_to_email_to_service_requests_table.php

    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->string('assigned_to_email')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn('assigned_to_email');
        });
    }

};
