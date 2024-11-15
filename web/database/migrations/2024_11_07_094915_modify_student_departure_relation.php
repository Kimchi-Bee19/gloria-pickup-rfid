<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->dropConstrainedForeignId("student_departure_log_id");
        });

        // Add the tracking to the student_departure_logs table instead
        Schema::table('student_departure_logs', function (Blueprint $table) {
            $table->foreignId('arrival_departure_tracking_id')->nullable()->after('student_identity_id')->constrained('arrival_departure_trackings')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('arrival_departure_trackings', function (Blueprint $table) {
            $table->foreignId('student_departure_log_id')->nullable()->constrained('student_departure_logs')->onDelete('cascade');
        });

        // Remove the tracking from the student_departure_logs table
        Schema::table('student_departure_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('arrival_departure_tracking_id');
        });
    }
};
