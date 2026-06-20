<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCstmOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cstm_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cstm_id')->nullable();
            $table->foreign('cstm_id')->references('id')->on('cstm_splies')->onDelete('set null');

            $table->string('order_no')->nullable()->unique();
            $table->date('order_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->enum('type', ['sale','purchase'])->default('sale'); // sales or purchase
            $table->enum('status', ['Pending','Confirmed','Completed','Cancelled'])->default('Pending');

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
        Schema::dropIfExists('cstm_orders');
    }
}