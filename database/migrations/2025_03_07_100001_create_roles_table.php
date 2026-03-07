<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->index('slug');
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
