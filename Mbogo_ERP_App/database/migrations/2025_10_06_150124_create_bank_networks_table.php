<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankNetworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_networks', function (Blueprint $table) {
            $table->id();
            // required ids per your spec
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            // core fields
            $table->enum('type',['Bank','Network'])->default('Bank');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('account_or_wallet')->nullable();
            $table->string('branch')->nullable();
            $table->enum('status',['Active','Deleted'])->default('Active');
            // indexes & foreign keys
            $table->index(['company_id','work_point_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('bank_networks');
    }
}
