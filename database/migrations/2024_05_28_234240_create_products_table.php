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
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('categorie_id')->nullable();

            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->timestamp('date_created')->nullable();
            $table->timestamp('date_modified')->nullable();
            $table->string('status')->nullable();
            $table->boolean('featured')->nullable();
            $table->string('catalog_visibility')->nullable();
            $table->text('description')->nullable();

            $table->text('short_description')->nullable();
            $table->string('sku')->nullable();
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('regular_price', 8, 2)->nullable();
            $table->decimal('sale_price', 8, 2)->nullable();
            $table->timestamp('date_on_sale_from')->nullable();
            $table->timestamp('date_on_sale_to')->nullable();
            $table->integer('total_sales')->nullable();
            $table->string('tax_status')->nullable();
            $table->string('tax_class')->nullable();
            $table->boolean('manage_stock')->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->string('stock_status')->nullable();
            $table->string('backorders')->nullable();
            $table->integer('low_stock_amount')->nullable();
            $table->boolean('sold_individually')->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('reviews_allowed')->nullable();
            $table->text('purchase_note')->nullable();
            $table->integer('menu_order')->nullable();
            $table->string('post_password')->nullable();
            $table->boolean('virtuall')->nullable();
            $table->boolean('downloadable')->nullable();
            $table->unsignedBigInteger('shipping_class_id')->nullable();
            $table->integer('download_limit')->nullable();
            $table->integer('download_expiry')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable();
            $table->integer('review_count')->nullable();
            $table->timestamps();
            $table->foreign('categorie_id')->references('id')->on('categories')->onDelete('cascade');
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
