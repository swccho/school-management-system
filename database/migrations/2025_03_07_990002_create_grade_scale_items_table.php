<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_scale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->unsignedBigInteger('grade_scale_id');
            $table->decimal('min_mark', 8, 2);
            $table->decimal('max_mark', 8, 2);
            $table->string('letter_grade', 10);
            $table->decimal('grade_point', 5, 2);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        Schema::table('grade_scale_items', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('grade_scale_id')->references('id')->on('grade_scales')->cascadeOnDelete();
            $table->index('school_id');
            $table->index('grade_scale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scale_items');
    }
};
