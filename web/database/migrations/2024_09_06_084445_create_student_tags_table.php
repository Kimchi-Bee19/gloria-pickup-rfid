<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('student_tags', function (Blueprint $table) {
            $table->id();
            $table->string('tag', 32)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('student_tags');
    }
};
