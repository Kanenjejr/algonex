<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanGuarantorsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_guarantors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_application_id');
            $table->foreign('loan_application_id')->references('id')->on('micro_loan_applications')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('relation_type', ['NextOfKin', 'Referral', 'Guarantor', 'Referee'])->default('Referee');
            $table->string('full_name');
            $table->string('phone_no')->nullable();
            $table->string('relationship')->nullable();
            $table->string('email')->nullable();
            $table->string('work_email')->nullable();
            $table->string('branch')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_guarantors');
    }
}