<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockBatchesTable extends Migration
{
    public function up()
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();

            // PRODUCT LINK
            $table->unsignedBigInteger('product_id');

            // BATCH DETAILS
            $table->string('batch_no');
            $table->decimal('qty', 15, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->nullable();

            // DATES
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();

            // SOURCE
            $table->string('source')->nullable(); // purchase / production

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('product_id');
            $table->index('batch_no');
            $table->index('source');
            $table->index('company_id');
            $table->index('work_point_id');
            $table->index('expiry_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_batches');
    }
}