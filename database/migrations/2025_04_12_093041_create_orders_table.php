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
            $table->string('customer_name');
            $table->string('cashier_name');
            $table->string('customer_email')->nullable();
            $table->integer('table_number');
            $table->enum('payment_method', ['Bank Transfer', 'Staff Meal', 'Marketting Voucher']);
            $table->string('discount_name')->nullable();
            $table->decimal('discount_value', total: 10, places: 2);
            $table->integer('voucher_quantity')->nullable();
            $table->decimal('subtotal', total: 10, places: 2);
            $table->decimal('grand_total', total: 10, places: 2);
            $table->enum('status', ['Pending', 'Done']);
            $table->enum('order_type', ['Dine In', 'Take Away']);
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
