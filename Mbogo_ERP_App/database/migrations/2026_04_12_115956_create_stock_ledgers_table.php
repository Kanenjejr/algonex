<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockLedgersTable extends Migration
{
    public function up()
    {
        Schema::create('stock_ledgers', function (Blueprint $table) {
            $table->id();

            // PRODUCT LINK
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // STOCK MOVEMENT TYPE
            $table->enum('type', ['IN', 'OUT', 'SALE', 'ADJUSTMENT']);

            $table->enum('transaction_type', [
                'purchase',
                'sale',
                'issue',
                'adjustment',
                'return',
                'transfer'
            ])->nullable();

            // QUANTITY DETAILS
            $table->decimal('qty_in', 15, 4)->default(0);
            $table->decimal('qty_out', 15, 4)->default(0);
            $table->decimal('balance', 15, 4)->default(0);

            // COST / VALUE
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_value', 18, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);

            // ACCOUNTING
            $table->string('account_code', 8)->nullable();

            // REFERENCE DETAILS
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description')->nullable();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('company_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // DATE
            $table->timestamp('date')->useCurrent();

            $table->timestamps();

            // INDEXES
            $table->index('product_id');
            $table->index('type');
            $table->index('transaction_type');
            $table->index('account_code');
            $table->index('company_id');
            $table->index('company_unit_id');
            $table->index('work_point_id');
            $table->index('date');
            $table->index(['product_id', 'reference_type', 'reference_id'], 'stock_ref_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_ledgers');
    }
}