<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('featured', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('title')->nullable(); // Theme title
            $table->string('featured_type')->nullable(); // featured_type Featured creator or Featured code
            $table->string('site')->nullable(); // this can be any site themeforest or codecaniyonn
            $table->string('link')->nullable(); // Theme link
            $table->string('by')->nullable(); // Theme by
            $table->string('author_link')->nullable(); // Author profile link
            $table->string('author_name')->nullable(); // Author display name
            $table->string('language_name')->nullable(); // Category name (renamed from 'language_name')
            $table->string('language_link')->nullable(); // Category link (renamed from 'language_link')
            $table->string('price')->nullable(); // Price (as string for consistency)
            $table->string('offer')->nullable(); // Offer, nullable
            $table->string('stars')->nullable(); // Star rating, nullable
            $table->string('reviews')->nullable(); // Star rating, nullable
            $table->string('sales')->nullable(); // Sales count
            $table->string('image')->nullable(); // Image URL
            $table->string('trending')->nullable(); // Image URL
            $table->string('item_id')->nullable();
            $table->string('single_url')->nullable();

            $table->timestamps(); // Created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured');
    }
};
