<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceItemsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();

            // INVOICE LINK
            $table->unsignedBigInteger('invoice_id');

            // ITEM SOURCE
            $table->string('item_type')->default('product'); // product / service / manual
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();

            // ITEM DETAILS
            $table->string('product_name');
            $table->text('description')->nullable();

            // QUANTITY / PRICE
            $table->decimal('qty', 15, 4)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('price', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('service_id')->references('id')->on('services')->nullOnDelete()->cascadeOnUpdate();

            // INDEXES
            $table->index('invoice_id');
            $table->index('item_type');
            $table->index('product_id');
            $table->index('service_id');
            $table->index('product_name');
            $table->index('unit');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
}
