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
        Schema::create('popular_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('categories');
            $table->string('item_id')->nullable();
            $table->string('name');
            $table->string('single_url');
            $table->text('image')->nullable();
            $table->string('by')->nullable();
            $table->string('author_link')->nullable();
            $table->string('author_name')->nullable();
            $table->string('language_name')->nullable();
            $table->string('language_link')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('offer', 8, 2)->nullable();
            $table->float('stars', 3, 2)->nullable();
            $table->integer('reviews')->nullable();
            $table->integer('sales')->nullable();
            $table->string('trending')->default('No');
            $table->string('total_sales', 50)->nullable();
            $table->string('last_update')->nullable();
            $table->string('published')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_items');
    }
};
