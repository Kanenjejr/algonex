<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIssPrdsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iss_prds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('prd_id')->nullable();
            $table->date('issue_date');
            $table->decimal('issue_qnty', 12, 2);
            $table->string('iss_unit');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('received_by')->nullable();
            $table->enum('status',['Active','Deleted'])->default('Active');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('order_id')->references('id')->on('prd_orders')->onDelete('cascade');
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
        Schema::dropIfExists('iss_prds');
    }
}
