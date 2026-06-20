<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductStocksTable extends Migration
{
    public function up()
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->decimal('current_stock', 15,2)->default(0);
            $table->decimal('minimum_stock', 15,2)->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_stocks');
    }
}
