<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerPaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('payment_no')->unique();
            $table->date('payment_date');

            // RELATIONS
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('proforma_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // PAYMENT DETAILS
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('USD');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->string('payment_method')->nullable(); // bank / cash / mobile
            $table->unsignedBigInteger('payment_account_id')->nullable();
            $table->string('receipt_no')->nullable();
            $table->string('receipt_attachment')->nullable();
            $table->text('notes')->nullable();

            // APPROVAL / LOCK
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->uuid('transaction_group')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('approval_comment')->nullable();
            $table->boolean('locked')->default(false);

            // AUDIT
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('proforma_id')->references('id')->on('proformas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('company_id')->references('id')->on('company_sites')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('business_unit_id')->references('id')->on('company_units')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('work_point_id')->references('id')->on('work_points')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('payment_account_id')->references('id')->on('accnt_subcharts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

            // INDEXES
            $table->index('payment_no');
            $table->index('payment_date');
            $table->index('invoice_id');
            $table->index('proforma_id');
            $table->index('customer_id');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('payment_method');
            $table->index('payment_account_id');
            $table->index('receipt_no');
            $table->index('status');
            $table->index('transaction_group');
            $table->index('approved_by');
            $table->index('locked');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_payments');
    }
}
