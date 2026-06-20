<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('pos_sale_items', function (Blueprint $table) {

    $table->id();
    $table->unsignedBigInteger('pos_sale_id');

    $table->unsignedBigInteger('product_id');

    $table->integer('qty');
    $table->decimal('price', 15,2);
    $table->decimal('total', 15,2);

    $table->timestamps();

    $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->cascadeOnDelete();
});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pos_sale_items');
    }
}
