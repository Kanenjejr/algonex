<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_applications', function (Blueprint $table) {
            $table->id();

            $table->string('application_no')->unique();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('applicant_id');
            $table->foreign('applicant_id')->references('id')->on('micro_loan_applicants')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('loan_category_id');
            $table->foreign('loan_category_id')->references('id')->on('micro_loan_categories')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('loan_product_id')->nullable();
            $table->foreign('loan_product_id')->references('id')->on('micro_loan_products')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->date('application_date')->nullable();
            $table->decimal('amount_applied', 18, 2)->default(0);
            $table->decimal('approved_amount', 18, 2)->default(0);
            $table->decimal('project_cost', 18, 2)->default(0);
            $table->decimal('own_contribution', 18, 2)->default(0);
            $table->integer('loan_period_months')->default(1);
            $table->decimal('monthly_repayment', 18, 2)->default(0);

            $table->decimal('interest_rate', 8, 2)->default(0);
            $table->enum('interest_method', ['flat', 'reducing'])->default('flat');

            $table->decimal('penalty_percent_per_day', 8, 2)->default(0);
            $table->enum('penalty_basis', ['full_loan', 'remaining_balance'])->default('remaining_balance');

            $table->decimal('reminder_charge', 18, 2)->default(0);
            $table->integer('sms_token_cost')->default(25);

            $table->date('expected_start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('cashout_date')->nullable();

            $table->text('purpose')->nullable();
            $table->text('notes')->nullable();

            $table->enum('verification_status', ['Pending', 'Verified', 'Declined'])->default('Pending');
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_remarks')->nullable();

            $table->enum('approval_status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_remarks')->nullable();

            $table->enum('disbursement_status', ['Pending', 'Cashed-Out'])->default('Pending');

            $table->enum('loan_status', ['Draft', 'Submitted', 'Verified', 'Declined', 'Approved', 'Rejected', 'Cashed-Out', 'Active', 'Closed', 'Defaulted'])->default('Draft');

            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_applications');
    }
}