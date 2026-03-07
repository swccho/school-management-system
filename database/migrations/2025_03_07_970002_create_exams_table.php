<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('academic_session_id');
            $table->unsignedBigInteger('exam_type_id');
            $table->string('name');
            $table->string('code')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->date('result_publish_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('academic_session_id')->references('id')->on('academic_sessions')->cascadeOnDelete();
            $table->foreign('exam_type_id')->references('id')->on('exam_types')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('academic_session_id');
            $table->index('exam_type_id');
            $table->index('start_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
