<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosSalesTable extends Migration
{
    public function up()
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();

            // POS / SALE DETAILS
            $table->string('receipt_no')->nullable();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // CUSTOMER
            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers')
                ->nullOnDelete();

            // USER / CASHIER
            $table->unsignedBigInteger('user_id')->nullable();

            // FINANCIAL DETAILS
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // PAYMENT DETAILS
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('change_returned', 15, 2)->default(0);

            // STATUS
            $table->string('status')->default('completed');

            // ACCOUNTING LINK
            $table->unsignedBigInteger('account_transaction_id')->nullable();

            $table->timestamps();

            // INDEXES
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('user_id');
            $table->index('account_transaction_id');
            $table->index('receipt_no');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_sales');
    }
}