<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSupplyStocksTable extends Migration
{
    public function up()
    {
        Schema::create('general_supply_stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('dept_id')->nullable();
            $table->foreign('dept_id')->references('id')->on('departments')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('section_id')->nullable();
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('stock_scope', ['Shared', 'Dedicated'])->default('Shared');

            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('general_supply_items')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('item_description_id');
            $table->foreign('item_description_id')->references('id')->on('general_supply_item_descriptions')->onDelete('cascade')->onUpdate('cascade');

            $table->date('expiry_date')->nullable();

            $table->decimal('qty_in', 18, 2)->default(0);
            $table->decimal('qty_out', 18, 2)->default(0);
            $table->decimal('damaged_qty', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);

            $table->decimal('purchase_price', 18, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();

            $table->unique([
                'company_id',
                'comp_unit_id',
                'work_point_id',
                'dept_id',
                'section_id',
                'stock_scope',
                'item_id',
                'item_description_id',
                'expiry_date'
            ], 'gs_stock_unique_row');
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_supply_stocks');
    }
}