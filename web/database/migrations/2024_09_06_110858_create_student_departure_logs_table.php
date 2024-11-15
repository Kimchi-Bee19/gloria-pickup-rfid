<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_departure_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('student_identity_id')->constrained('student_identities')->onDelete('cascade');
            $table->timestamp('departure_time')->default(DB::raw('CURRENT_TIMESTAMP'))->index();
            $table->enum('method', ['manual', 'rfid'])->default('manual');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_departure_logs');
    }
};
