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
            $table->string('status')->nullable();
            $table->string('currency')->nullable();
            $table->string('version')->nullable();
            $table->boolean('prices_include_tax')->nullable();
            $table->dateTime('date_created')->nullable();
            $table->dateTime('date_modified')->nullable();
            $table->decimal('discount_total', 10, 2)->nullable();
            $table->decimal('discount_tax', 10, 2)->nullable();
            $table->decimal('shipping_total', 10, 2)->nullable();
            $table->decimal('shipping_tax', 10, 2)->nullable();
            $table->decimal('cart_tax', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('total_tax', 10, 2)->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('order_key')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_method_title')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('customer_ip_address')->nullable();
            $table->string('customer_user_agent')->nullable();
            $table->string('created_via')->nullable();
            $table->text('customer_note')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->dateTime('date_paid')->nullable();
            $table->string('cart_hash')->nullable();
            $table->string('number')->nullable();
            $table->string('payment_url')->nullable();
            $table->boolean('is_editable')->nullable();
            $table->boolean('needs_payment')->nullable();
            $table->boolean('needs_processing')->nullable();
            $table->dateTime('date_created_gmt')->nullable();
            $table->dateTime('date_modified_gmt')->nullable();
            $table->dateTime('date_completed_gmt')->nullable();
            $table->dateTime('date_paid_gmt')->nullable();
            $table->string('currency_symbol')->nullable();
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
