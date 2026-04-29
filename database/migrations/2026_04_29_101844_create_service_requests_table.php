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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('service_type'); // e.g., 'Cassa Edile', 'Edilcassa'
            $table->string('service_name');
            $table->text('service_description');
            $table->string('status')->default('Inviato'); // 'Inviato', 'Visionato', 'In elaborazione', 'Concluso'
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamps(); // For updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
