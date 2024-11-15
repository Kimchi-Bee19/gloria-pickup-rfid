<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('pickup_personnels', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 256);
            $table->boolean('receive_notifications')->default(false);
            $table->string('phone_number', 16)->nullable();
            $table->string('picture_url', 256)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pickup_personnels');
    }
};
