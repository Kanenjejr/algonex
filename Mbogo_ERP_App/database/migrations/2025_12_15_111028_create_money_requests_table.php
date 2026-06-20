<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('money_requests', function (Blueprint $table) {
            $table->id();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('company_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // USER / REQUESTER
            $table->unsignedBigInteger('User_id')->nullable();

            // ACCOUNT AND DEPARTMENT
            $table->foreignId('account_id')->constrained('accnt_charts')->restrictOnDelete();
            $table->unsignedBigInteger('sub_account_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();

            // REQUEST DETAILS
            $table->string('RequestNo')->unique();
            $table->date('RequestDate')->nullable();
            $table->string('PayeeName')->nullable();
            $table->string('PayeeContact')->nullable();
            $table->text('Description')->nullable();
            $table->double('total_amount')->default(0);

            // WORKFLOW STATUS
            $table->string('Status')->default('Pending');

            // VERIFICATION
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->text('verified_comment')->nullable();

            // APPROVAL
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comment')->nullable();
            $table->double('approved_amount')->nullable();

            // REJECTION
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_comment')->nullable();

            // PAYMENT / CASHING
            $table->string('Payment_mode')->nullable();
            $table->unsignedBigInteger('cashed_by')->nullable();
            $table->timestamp('cashed_at')->nullable();
            $table->string('payment_vocher_no')->nullable();
            $table->text('cashier_comment')->nullable();

            // RETIREMENT
            $table->unsignedBigInteger('retired_by')->nullable();
            $table->timestamp('retired_at')->nullable();
            $table->text('retirement_comment')->nullable();
            $table->string('retirement_docs')->nullable();
            $table->double('returned_amount')->default(0);

            // OTHER
            $table->text('remarks')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('company_unit_id')
                ->references('id')
                ->on('company_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('User_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('sub_account_id')
                ->references('id')
                ->on('accnt_subcharts')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('verified_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('rejected_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cashed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('retired_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('company_id');
            $table->index('company_unit_id');
            $table->index('work_point_id');
            $table->index('User_id');
            $table->index('account_id');
            $table->index('sub_account_id');
            $table->index('department_id');
            $table->index('section_id');
            $table->index('Status');
            $table->index('RequestDate');
        });
    }

    public function down()
    {
        Schema::dropIfExists('money_requests');
    }
}