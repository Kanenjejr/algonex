<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanDisbursementsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_disbursements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_application_id');
            $table->foreign('loan_application_id')->references('id')->on('micro_loan_applications')->onDelete('cascade')->onUpdate('cascade');

            $table->date('disbursement_date');
            $table->decimal('amount_disbursed', 18, 2)->default(0);
            $table->string('channel')->nullable();
            $table->string('reference_no')->nullable();
            $table->string('bank_or_network')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('disbursed_by')->nullable();
            $table->foreign('disbursed_by')->references('id')->on('users')->onDelete('set null');

            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_disbursements');
    }
}