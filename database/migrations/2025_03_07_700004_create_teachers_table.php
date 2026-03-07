<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('staff_id');
            $table->string('teacher_code')->nullable();
            $table->string('qualification')->nullable();
            $table->string('specialization')->nullable();
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->boolean('is_class_teacher')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('staff_id')->references('id')->on('staffs')->cascadeOnDelete();
            $table->unique('staff_id');
            $table->index('school_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
