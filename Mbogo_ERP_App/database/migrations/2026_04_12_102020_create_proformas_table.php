<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProformasTable extends Migration
{
    public function up()
    {
        Schema::create('proformas', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('proforma_no')->unique();

            // RELATIONS / ERP CORE LINKS
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('business_unit_id');
            $table->unsignedBigInteger('work_point_id');

            // FINANCIAL
            $table->decimal('subtotal', 15, 4)->default(0);
            $table->decimal('vat', 15, 4)->default(0);
            $table->decimal('total', 15, 4)->default(0);

            // INVOICE / BANK DETAILS
            $table->string('invoice_type')->nullable();
            $table->string('currency', 10)->default('TZS')->index();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('branch')->nullable();

            // PROFORMA STATUS
            $table->string('status')->default('draft');

            // PAYMENT TRACKING
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 15, 4)->default(0);

            // ACCOUNTING / APPROVAL LOCK
            $table->uuid('accounting_transaction_group')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            // AUDIT
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEYS
            $table->foreign('customer_id')->references('id')->on('customers')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('company_id')->references('id')->on('company_sites')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('business_unit_id')->references('id')->on('company_units')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('work_point_id')->references('id')->on('work_points')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('bank_id')->references('id')->on('accnt_subcharts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

            // INDEXES
            $table->index('proforma_no');
            $table->index('customer_id');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('invoice_type');
            $table->index('bank_id');
            $table->index('status');
            $table->index('payment_status');
            $table->index('created_by');
            $table->index('approved_by');
            $table->index('accounting_transaction_group');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('proformas');
    }
}
