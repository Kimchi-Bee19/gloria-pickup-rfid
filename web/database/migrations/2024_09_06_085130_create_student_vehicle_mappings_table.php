<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('student_vehicle_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained("students")->cascadeOnDelete()->foreign('student_id_fk');
            $table->foreignId('vehicle_id')->constrained("vehicles")->cascadeOnDelete()->foreign('vehicle_id_fk');
            $table->timestamps();

            $table->unique(['student_id', 'vehicle_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_vehicle_mappings');
    }
};
