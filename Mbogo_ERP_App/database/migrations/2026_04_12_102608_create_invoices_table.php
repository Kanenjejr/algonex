<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('invoice_no')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->date('agreement_date')->nullable();

            // INVOICE DETAILS
            $table->string('invoice_type')->nullable();
            $table->string('reference_no')->nullable();

            // RELATIONS
            $table->unsignedBigInteger('proforma_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('contact_id')->nullable();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // BANK DETAILS TO PRINT ON INVOICE
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('branch')->nullable();

            // CURRENCY / EXCHANGE
            $table->string('currency', 10)->default('USD');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->decimal('total_tzs', 15, 4)->nullable();

            // FINANCIAL
            $table->decimal('sub_total', 15, 4)->default(0);
            $table->decimal('vat_rate', 10, 4)->default(0);
            $table->decimal('tax', 15, 4)->default(0);
            $table->boolean('vat_inclusive')->default(false);
            $table->decimal('discount', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            // PAYMENT TRACKING
            $table->string('payment_type')->nullable(); // full / partial / credit
            $table->decimal('paid_amount', 15, 4)->default(0);
            $table->decimal('balance', 15, 4)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            // STATUS / SYSTEM FLAGS
            $table->enum('status', ['unpaid', 'partial', 'paid', 'cancelled'])->default('unpaid');
            $table->boolean('stock_posted')->default(false);
            $table->boolean('has_delivery')->default(false);
            $table->boolean('locked')->default(false);

            // AUDIT
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('proforma_id')->references('id')->on('proformas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('company_id')->references('id')->on('company_sites')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('business_unit_id')->references('id')->on('company_units')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('work_point_id')->references('id')->on('work_points')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('bank_id')->references('id')->on('accnt_subcharts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

            // INDEXES
            $table->index('invoice_no');
            $table->index('invoice_date');
            $table->index('due_date');
            $table->index('agreement_date');
            $table->index('invoice_type');
            $table->index('reference_no');
            $table->index('proforma_id');
            $table->index('customer_id');
            $table->index('contact_id');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('bank_id');
            $table->index('currency');
            $table->index('payment_type');
            $table->index('vat_inclusive');
            $table->index('payment_status');
            $table->index('status');
            $table->index('stock_posted');
            $table->index('has_delivery');
            $table->index('locked');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
