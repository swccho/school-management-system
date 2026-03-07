<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_subject_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('result_summary_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('full_marks', 10, 2)->default(0);
            $table->decimal('obtained_marks', 10, 2)->default(0);
            $table->string('grade_letter', 10)->nullable();
            $table->decimal('grade_point', 5, 2)->nullable();
            $table->string('pass_status')->default('fail');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('result_subject_details', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('result_summary_id')->references('id')->on('result_summaries')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('result_summary_id');
            $table->index('subject_id');
            $table->unique(['result_summary_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_subject_details');
    }
};
