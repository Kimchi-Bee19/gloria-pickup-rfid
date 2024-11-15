<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vehicle_identities', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['uhf_rfid'])->default('uhf_rfid');
            $table->binary('tag_id', 12)->index();
            $table->binary('auth_check', 64);
            $table->string('notes', 256);

            $table->foreignId('vehicle_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicle_identities');
    }
};
