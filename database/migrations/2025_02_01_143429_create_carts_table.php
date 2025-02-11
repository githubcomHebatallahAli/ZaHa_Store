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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'canceled'])->default('active')->nullable();
            $table->foreignId('code_id')->nullable()->constrained('codes')->onDelete('set null');
            $table->decimal('totalPrice', 15, 2)->default(0);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('shippingCost', 10, 2)->nullable();
            $table->decimal('finalPrice', 10, 2)->nullable();
            $table->decimal('profit', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
