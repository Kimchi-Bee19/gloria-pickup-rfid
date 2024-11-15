<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_departure_logs', function (Blueprint $table) {
            $table->foreignId('student_identity_id')->nullable()->change();
        });

        Schema::table('vehicle_arrival_logs', function (Blueprint $table) {
            $table->foreignId('vehicle_identity_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_departure_logs', function (Blueprint $table) {
            $table->foreignId('student_identity_id')->nullable(false)->change();
        });

        Schema::table('vehicle_arrival_logs', function (Blueprint $table) {
            $table->foreignId('vehicle_identity_id')->nullable(false)->change();
        });
    }
};
