<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
  public function up()
{
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();

        // 🔹 ORGANIZATION STRUCTURE
        $table->unsignedBigInteger('company_id')->nullable();
        $table->unsignedBigInteger('branch_id')->nullable();
        $table->unsignedBigInteger('business_unit_id')->nullable();

        // 🔹 SALE IDENTITY
        $table->string('sale_number')->unique(); // INV-0001
        $table->enum('sale_type', ['pos', 'invoice', 'order'])->default('pos');

        // 🔹 CUSTOMER
        $table->unsignedBigInteger('customer_id')->nullable();
        $table->string('customer_name')->nullable(); // walk-in

        // 🔹 FINANCIAL BREAKDOWN
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->decimal('discount_amount', 15, 2)->default(0);
        $table->decimal('tax_amount', 15, 2)->default(0);
        $table->decimal('total_amount', 15, 2)->default(0);

        // 🔹 PAYMENT TRACKING
        $table->decimal('paid_amount', 15, 2)->default(0);
        $table->decimal('balance', 15, 2)->default(0);

        $table->enum('payment_method', ['cash', 'mobile', 'bank', 'credit'])->nullable();
        $table->enum('payment_status', ['paid', 'partial', 'pending'])->default('pending');

        // 🔹 REFERENCES (INTEGRATION)
        $table->unsignedBigInteger('sales_order_id')->nullable();
        $table->unsignedBigInteger('invoice_id')->nullable();

        // 🔹 STATUS CONTROL
        $table->enum('status', ['draft', 'completed', 'cancelled'])->default('completed');

        // 🔹 DATES
        $table->timestamp('sale_date')->useCurrent();

        // 🔹 AUDIT
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('approved_by')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
