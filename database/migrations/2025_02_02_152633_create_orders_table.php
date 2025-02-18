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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->cascadeOnDelete();
            $table->string('name');
            $table->string('phoNum');
            $table->string('address');
            $table->string('details')->nullable();
            $table->unsignedBigInteger('orderProductCount')->default(0);
            // $table->decimal('totalPrice', 15, 2)->default(0);
            // $table->decimal('discount', 10, 2)->nullable();
            // $table->decimal('shippingCost', 10, 2)->nullable();
            // $table->decimal('finalPrice', 10, 2)->nullable();
            // $table->decimal('profit', 10, 2)->nullable();
            $table->enum('status', ['pending','approve','compeleted','canceled'])->default('pending')->nullable();
            $table->timestamp('creationDate')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
