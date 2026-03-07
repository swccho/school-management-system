<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('short_name')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->boolean('has_practical')->default(false);
            $table->unsignedSmallInteger('full_marks')->nullable();
            $table->unsignedSmallInteger('pass_marks')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->index('school_id');
            $table->index('status');
            $table->index('type');
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
