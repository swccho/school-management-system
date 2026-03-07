<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('default_language')->nullable();
            $table->string('timezone')->nullable();
            $table->string('date_format')->nullable();
            $table->string('time_format')->nullable();
            $table->string('default_currency')->nullable();
            $table->string('attendance_mode')->nullable();
            $table->string('result_publish_policy')->nullable();
            $table->string('theme')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->timestamps();
        });

        Schema::table('school_settings', function (Blueprint $table) {
            $table->unique('school_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
