<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_arrival_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->foreignId('vehicle_identity_id')->constrained('vehicle_identities')->onDelete('cascade');
            $table->timestamp('arrival_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->enum('method', ['manual', 'rfid'])->default('manual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_arrival_logs');
    }
};
