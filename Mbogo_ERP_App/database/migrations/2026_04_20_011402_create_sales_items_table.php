<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_items', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('sale_id');

    $table->unsignedBigInteger('product_id');
    $table->string('product_name');

    $table->decimal('quantity', 10, 2);
    $table->decimal('price', 15, 2);
    $table->decimal('total', 15, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_items');
    }
}
