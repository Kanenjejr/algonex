<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsReceiptItemsTable extends Migration
{
   public function up()
{
    Schema::create('goods_receipt_items', function (Blueprint $table) {

        $table->id();

        //  RELATION
        $table->unsignedBigInteger('goods_receipt_id');
        $table->unsignedBigInteger('product_id');

        //  QTY
        $table->decimal('ordered_qty', 18,2)->default(0);
        $table->decimal('received_qty', 18,2)->default(0);

        //  PRICE
        $table->decimal('unit_price', 18,2)->default(0);
        $table->decimal('total_price', 18,2)->default(0);

        //  ACCOUNT
        $table->string('account_code')->nullable();
        $table->string('account_name')->nullable();

        $table->timestamps();

        // FK
        $table->foreign('goods_receipt_id')
              ->references('id')->on('goods_receipts')
              ->cascadeOnDelete();

        $table->foreign('product_id')
              ->references('id')->on('products')
              ->cascadeOnDelete();
    });
}
    public function down()
    {
        Schema::dropIfExists('goods_receipt_items');
    }
}
