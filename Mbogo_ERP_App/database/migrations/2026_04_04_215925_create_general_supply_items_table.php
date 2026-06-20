<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSupplyItemsTable extends Migration
{
    public function up()
    {
        Schema::create('general_supply_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['item_name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_supply_items');
    }
}