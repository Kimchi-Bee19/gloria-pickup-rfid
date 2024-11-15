<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('student_tag_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained("students")->cascadeOnDelete()->foreign('student_id_fk');
            $table->foreignId('tag_id')->constrained("student_tags")->cascadeOnDelete()->foreign('tag_id_fk');
            $table->timestamps();

            $table->unique(['student_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_tag_mappings');
    }
};
