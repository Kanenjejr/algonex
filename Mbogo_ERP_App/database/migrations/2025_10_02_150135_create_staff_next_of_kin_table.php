<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffNextOfKinTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_next_of_kin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // staff user id
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->string('name');
            $table->string('relationship')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive, Deleted
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
        Schema::dropIfExists('staff_next_of_kin');
    }
}