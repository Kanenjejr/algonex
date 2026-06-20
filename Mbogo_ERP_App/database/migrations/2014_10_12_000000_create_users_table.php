<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->string('username');
            $table->string('name');
            $table->string('gender');
            $table->string('phone_No')->nullable();
            $table->string('email')->nullable();
            $table->string('image')->nullable();
            $table->string('st_sign')->nullable();
            $table->string('role')->default('Unknown');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('status')->default('Active');
            $table->decimal('gross_salary',18,2)->default(0);
            $table->string('accName')->default('N/A');
            $table->string('accNo')->default('N/A');
            $table->string('nssfNo')->default('N/A');
            $table->string('wcfNo')->default('N/A');
            $table->string('NHIF')->default('N/A');
            $table->string('TIN')->default('N/A');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}