<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_settings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->decimal('sms_token_cost', 18, 2)->default(25.00);
            $table->decimal('default_reminder_charge', 18, 2)->default(0);
            $table->enum('default_penalty_basis', ['full_loan', 'remaining_balance'])->default('remaining_balance');
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_settings');
    }
}