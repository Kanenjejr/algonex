<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSupplyItemDescriptionsTable extends Migration
{
    public function up()
    {
        Schema::create('general_supply_item_descriptions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')
                ->references('id')
                ->on('general_supply_items')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->string('description_name'); // mfano: Afya, Jambo
            $table->string('unit_name'); // mfano: Carton, Bottle, Piece, Box
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['item_id', 'description_name', 'unit_name'], 'gs_item_desc_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_supply_item_descriptions');
    }
}