<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesOrderItemsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->unsignedBigInteger('sales_order_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('product_name')->nullable();
            $table->string('product_code')->nullable();

            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();

            $table->decimal('qty', 15, 4)->default(0);
            $table->string('unit_of_measure')->nullable();

            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);

            $table->decimal('cost_price', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);

            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('company_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->string('company_code')->nullable();
            $table->string('company_name')->nullable();

            $table->string('business_code')->nullable();
            $table->string('business_name')->nullable();

            $table->string('location_code')->nullable();
            $table->string('location_name')->nullable();

            $table->string('status')->default('ACTIVE');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->index('sales_order_id');
            $table->index('product_id');
            $table->index('company_id');
            $table->index('company_unit_id');
            $table->index('work_point_id');
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            if (Schema::hasTable('sales_orders')) {
                $table->foreign('sales_order_id')
                    ->references('id')
                    ->on('sales_orders')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('products')) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('company_sites')) {
                $table->foreign('company_id')
                    ->references('id')
                    ->on('company_sites')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('company_units')) {
                $table->foreign('company_unit_id')
                    ->references('id')
                    ->on('company_units')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('work_points')) {
                $table->foreign('work_point_id')
                    ->references('id')
                    ->on('work_points')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }

            if (Schema::hasTable('users')) {
                $table->foreign('created_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();

                $table->foreign('updated_by')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete()
                    ->cascadeOnUpdate();
            }
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_order_items');
    }
}