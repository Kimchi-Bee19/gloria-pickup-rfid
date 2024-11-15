<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('identity_readers', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('username')->unique();
            $table->string('password_hash');
            $table->enum('type', ['student_rfid', 'vehicle_rfid', 'superuser', 'external']);
            $table->timestamp('last_login')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('identity_readers');
    }
};
