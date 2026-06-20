<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccntSubchartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accnt_subcharts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            // master chart reference
            $table->unsignedBigInteger('accnt_chart_id');
            $table->foreign('accnt_chart_id')->references('id')->on('accnt_charts')->onDelete('cascade')->onUpdate('cascade');
            $table->string('SubCode'); // code for sub account
            $table->string('SubDescription')->nullable();
            $table->enum('Status', ['Active','Deleted'])->default('Active');
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
        Schema::dropIfExists('accnt_subcharts');
    }
}
