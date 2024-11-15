<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('student_pickup_personnel_mappings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pickup_personnel_id')->unsigned();
            $table->bigInteger('student_id')->unsigned();
            $table->foreign('pickup_personnel_id')->references('id')->on('pickup_personnels')->name( 'pickup_personnel_id_fk');
            $table->foreign('student_id')->references('id')->on('students')->name('student_id_fk');
            $table->string('relationship_to_student', 32);
            $table->timestamps();

            $table->unique(['pickup_personnel_id', 'student_id'], 'pickup_student_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_pickup_personnel_mappings');
    }
};
