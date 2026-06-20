<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // USER / CREATOR
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // PRODUCT DETAILS
            $table->string('product_name');
            $table->string('product_size')->nullable();

            // INVENTORY / COSTING
            $table->decimal('avg_cost', 15, 2)->default(0);
            $table->decimal('total_qty', 15, 2)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->decimal('reorder_level', 15, 2)->default(10);
            $table->decimal('opening_stock', 15, 2)->default(0);

            // ACCOUNTING - COGS
            $table->string('cogs_account_code')->nullable();
            $table->foreignId('cogs_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // ACCOUNTING - INVENTORY
            $table->string('inventory_account_code')->nullable();
            $table->foreignId('inventory_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // ACCOUNTING - REVENUE
            $table->string('revenue_account_code')->nullable();
            $table->foreignId('revenue_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            // STATUS
            $table->string('status')->default('Active');

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('comp_unit_id')
                ->references('id')
                ->on('company_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('user_id');
            $table->index('company_id');
            $table->index('comp_unit_id');
            $table->index('work_point_id');
            $table->index('product_name');
            $table->index('cogs_account_code');
            $table->index('cogs_account_id');
            $table->index('inventory_account_code');
            $table->index('inventory_account_id');
            $table->index('revenue_account_code');
            $table->index('revenue_account_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}