<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->text('content');
            $table->foreignId('category_id')->constrained('notice_categories')->cascadeOnDelete();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('notices', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['school_id', 'slug']);
            $table->index('publish_date');
            $table->index('status');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
