<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender');
            $table->string('email')->unique();
            $table->string('phone');
            $table->date('date_of_birth');
            $table->string('address');
            $table->unsignedBigInteger('class_id')->nullable();
            $table->integer('score')->default(0);
            $table->string('status')->default('active');
            $table->string('image')->nullable();
            $table->foreign('class_id')->references('id')->on('table_classes')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_students');
    }
};
