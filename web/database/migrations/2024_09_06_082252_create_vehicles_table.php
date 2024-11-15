<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('model', 32)->nullable();
            $table->string('color', 32)->nullable();
            $table->string('license_plate', 16);
            $table->date('license_plate_expiry')->nullable();
            $table->string('picture_url', 256)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};
