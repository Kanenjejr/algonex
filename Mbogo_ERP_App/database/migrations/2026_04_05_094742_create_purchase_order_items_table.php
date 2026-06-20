<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_order_id');

            $table->enum('item_type', [
                'GeneralSupply',
                'RawMaterial'
            ])->default('GeneralSupply');

            $table->unsignedBigInteger('item_id')->nullable();

            $table->string('item_name');
            $table->text('description')->nullable();
            $table->string('unit')->nullable();

            $table->decimal('qty', 18, 2)->default(0);
            $table->decimal('received_qty', 18, 2)->default(0);
            $table->decimal('balance_qty', 18, 2)->default(0);

            $table->decimal('unit_price', 18, 2)->default(0);
            $table->decimal('sub_total', 18, 2)->default(0);
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('total_price', 18, 2)->default(0);

            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();

            $table->timestamps();

            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index('purchase_order_id');
            $table->index('item_type');
            $table->index('item_id');
            $table->index('account_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
}