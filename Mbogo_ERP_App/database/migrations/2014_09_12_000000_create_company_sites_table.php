<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanySitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_sites', function (Blueprint $table) {
            $table->id();
            $table->string('company_code');
            $table->string('company_name');
            $table->string('Type');
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('TIN')->nullable();
            $table->string('phone_No');
            $table->string('logo')->nullable();
            $table->string('user_id')->nullable();
            $table->string('signature')->nullable();
            $table->string('stamp')->nullable();
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('company_sites');
    }
}