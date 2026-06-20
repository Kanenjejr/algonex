<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHiredEquipmentWorkingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hired_equipment_workings', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('hired_equipment_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->decimal('WorkingHours', 6, 2)->default(0.00);
            $table->decimal('Minutes', 6, 2)->default(0.00);
            $table->decimal('TotalPrice', 10, 2)->default(0.00);
            $table->date('WorkingDate')->nullable();
            $table->time('TimeIn')->nullable();
            $table->time('TimeOut')->nullable();
            $table->string('PaymentStatus')->default('Pending');
            $table->string('Status')->default('Active');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('hired_equipment_id')->references('id')->on('hired_equipment')->onDelete('cascade');
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
        Schema::dropIfExists('hired_equipment_workings');
    }
}
