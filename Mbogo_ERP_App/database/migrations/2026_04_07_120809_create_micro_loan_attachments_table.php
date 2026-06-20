<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanAttachmentsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('micro_loan_attachments')) {

            Schema::create('micro_loan_attachments', function (Blueprint $table) {

                $table->id(); // 🔥 muhimu sana

                // Loan Application
                $table->foreignId('loan_application_id')
                      ->nullable()
                      ->constrained('micro_loan_applications')
                      ->cascadeOnDelete();

                // Repayment
                $table->foreignId('repayment_id')
                      ->nullable()
                      ->constrained('micro_loan_repayments')
                      ->cascadeOnDelete();

                // Attachment Type
                $table->enum('attachment_type', [
                    'ApplicationLetter',
                    'Contract',
                    'AssetDocument',
                    'BorrowerImage',
                    'RefereeImage',
                    'NationalID',
                    'Passport',
                    'BusinessCertificate',
                    'TCC',
                    'LogBook',
                    'CourtOrder',
                    'RepaymentSlip',
                    'Other'
                ])->default('Other');

                // File Info
                $table->string('file_name');
                $table->string('file_path');
                $table->string('file_ext')->nullable();

                // Uploaded By
                $table->string('uploaded_by_name')->nullable();

                $table->foreignId('uploaded_by')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();

                // Status
                $table->string('status')->default('Active');

                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_attachments');
    }
}