<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('school_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('summary')->nullable();
            $table->text('content');
            $table->foreignId('category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('news_posts', function (Blueprint $table) {
            $table->foreign('school_id')->references('id')->on('schools')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['school_id', 'slug']);
            $table->index('status');
            $table->index('published_at');
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_posts');
    }
};
