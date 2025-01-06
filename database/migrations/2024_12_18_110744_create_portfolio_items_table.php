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
        Schema::create('portfolio_items', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->text('image')->nullable();
            $table->string('name')->nullable();
            $table->text('category')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->string('ratings')->nullable();
            $table->string('sales')->nullable();
            $table->float('rating', 3, 2)->nullable();
            $table->string('total_ratings')->nullable();
            $table->string('total_sales')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_items');
    }
};
