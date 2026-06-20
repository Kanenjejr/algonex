<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCstmProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cstm_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('cstm_orders')->onDelete('cascade');

            $table->unsignedBigInteger('cstm_id')->nullable();
            $table->foreign('cstm_id')->references('id')->on('cstm_splies')->onDelete('set null');

            $table->unsignedBigInteger('product_id')->nullable(); // FK to products table if present
            $table->string('product_name')->nullable();
            $table->decimal('quantity', 14, 4)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 15, 4)->default(0);
            $table->decimal('total_price', 18, 4)->default(0);

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cstm_products');
    }
}
