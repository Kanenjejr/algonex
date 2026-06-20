<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjustmentsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();

            // PRODUCT LINK
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // ADJUSTMENT DETAILS
            $table->decimal('qty', 15, 2)->default(0);
            $table->enum('type', ['increase', 'decrease']);
            $table->text('reason')->nullable();

            $table->timestamps();

            // INDEXES
            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_adjustments');
    }
}