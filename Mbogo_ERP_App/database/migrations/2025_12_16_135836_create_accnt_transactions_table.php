<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccntTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('accnt_transactions', function (Blueprint $table) {
            $table->id();

            // GROUP / REFERENCE
            $table->uuid('transaction_group')->index();
            $table->string('pcv_no')->nullable();
            $table->date('trans_date');
            $table->string('reference')->unique();
            $table->string('check_no')->nullable();
            $table->string('request_no')->nullable();
            $table->unsignedBigInteger('requisition_id')->nullable();

            // TRANSACTION TYPE
            $table->enum('category', ['Cash','Bank','Purchase Order','Proforma','Invoice','Sales','Payment','Receipt','Journal']);
            $table->string('currency', 10)->nullable();
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->text('memo')->nullable();
            $table->string('payee')->nullable();

            // ERP CORE LINKS
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // ACCOUNT AND DEPARTMENT
            $table->foreignId('account_id')->constrained('accnt_charts')->restrictOnDelete();
            $table->unsignedBigInteger('sub_account_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();

            // DEBIT / CREDIT
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->decimal('source_amount', 15, 2)->default(0);
            $table->boolean('imported_from_excel')->default(false);

            // STATUS
            $table->enum('Status', ['Active', 'Deleted'])->default('Active');
            $table->softDeletes();

            // VERIFICATION
            $table->enum('verified', ['pending', 'verified'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_comment')->nullable();

            // APPROVAL
            $table->enum('approved', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_comment')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->restrictOnDelete()
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

            $table->foreign('requisition_id')
                ->references('id')
                ->on('money_requests')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('pcv_no');
            $table->index('trans_date');
            $table->index('request_no');
            $table->index('requisition_id');
            $table->index('category');
            $table->index('company_id');
            $table->index('work_point_id');
            $table->index('account_id');
            $table->index('sub_account_id');
            $table->index('department_id');
            $table->index('section_id');
            $table->index('type');
            $table->index('Status');
            $table->index('verified');
            $table->index('approved');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accnt_transactions');
    }
}