<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('table_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('description')->nullable();
            $table->string('status')->default('pending');
            $table->foreign('student_id')->references('id')->on('table_students')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('table_subjects')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('table_payments');
    }
};
