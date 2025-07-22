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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Foreign key to users table
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // 8 total digits, 2 after decimal point
            $table->decimal('discountPrice', 8, 2)->nullable();
            $table->string('status')->default('available'); // e.g., 'available', 'out_of_stock', 'draft'
            $table->json('images')->nullable(); // Store image paths/URLs as JSON array
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
