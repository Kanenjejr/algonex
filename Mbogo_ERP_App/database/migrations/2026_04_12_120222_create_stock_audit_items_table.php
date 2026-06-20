<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAuditItemsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_audit_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_audit_id')
                ->constrained('stock_audits')
                ->cascadeOnDelete();

            // GeneralSupply / RawMaterial / Product
            $table->string('item_type')->nullable();

            // ID from general_supply_items, raw_materials, or products depending on item_type
            $table->unsignedBigInteger('item_id')->nullable();

            $table->decimal('system_qty', 15, 4)->default(0);
            $table->decimal('physical_qty', 15, 4)->default(0);
            $table->decimal('counted_qty', 15, 4)->default(0);
            $table->decimal('variance_qty', 15, 4)->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->index('stock_audit_id');
            $table->index('item_type');
            $table->index('item_id');
            $table->index('variance_qty');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_audit_items');
    }
}