<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveryItemsTable extends Migration
{
    public function up()
    {
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('item_name')->nullable();
            $table->string('unit')->nullable();

            $table->decimal('quantity', 15, 4)->default(0);

            // NEW PACKING LIST / CUSTOMS MANIFEST ITEM FIELDS
            $table->string('packages_no_type')->nullable();
            $table->string('gross_weight')->nullable();

            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);
            $table->decimal('issued_qty', 15, 4)->default(0);

            $table->timestamps();

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('delivery_id');
            $table->index('product_id');
            $table->index('item_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('delivery_items');
    }
}