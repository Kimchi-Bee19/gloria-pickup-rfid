<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('mqtt_clients');
    }

    public function down(): void
    {
        Schema::create('mqtt_clients', function (Blueprint $table) {
            $table->id();
            $table->string('username', 256)->unique();
            $table->string('password_hash', 256);
            $table->boolean('is_admin');
            $table->timestamps();
        });
    }
};
