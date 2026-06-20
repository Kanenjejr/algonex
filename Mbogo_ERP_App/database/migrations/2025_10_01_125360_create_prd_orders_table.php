<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePrdOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('prd_orders', function (Blueprint $table) {
            $table->id();
            $table->date('ord_date');
            $table->decimal('ord_qnty', 12, 2);
            $table->decimal('iss_qnty', 12, 2)->default(0);
            $table->decimal('uniss_qnty', 12, 2);
            $table->string('ord_unit');
            $table->unsignedBigInteger('prd_id');
            $table->string('customer_name');
            $table->string('phone_no');
            $table->string('location');
            $table->unsignedBigInteger('user_id');
            $table->enum('status', ['Active','Deleted'])->default('Active');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('prd_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('prd_orders');
    }
}