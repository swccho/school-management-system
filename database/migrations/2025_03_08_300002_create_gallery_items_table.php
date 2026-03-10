<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('media_type');
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::table('gallery_items', function (Blueprint $table) {
            $table->index('sort_order');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
