<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('homework_id');
            $table->unsignedBigInteger('student_id');
            $table->dateTime('submitted_at');
            $table->string('file_path')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('submitted');
            $table->decimal('grade', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->timestamps();
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->foreign('homework_id')->references('id')->on('homework')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->unique(['homework_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
    }
};
