<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('arrival_departure_trackings', function (Blueprint $table) {
            $table->id();

            // Arrival and departures
            $table->foreignId('vehicle_arrival_log_id')->constrained('vehicle_arrival_logs')->onDelete('cascade');
            $table->foreignId('student_departure_log_id')->nullable()->constrained('student_departure_logs')->onDelete('cascade');

            // Announced
            $table->smallInteger('announced_count')->default(0);
            $table->timestamp('last_announced_time')->nullable();

            // Is active
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arrival_departure_trackings');
    }
};
