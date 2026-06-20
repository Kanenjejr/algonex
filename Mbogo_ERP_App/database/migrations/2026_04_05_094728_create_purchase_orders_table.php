<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | ERP STRUCTURE: COMPANY -> UNIT -> LOCATION / WORK POINT
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('business_unit_id');
            $table->unsignedBigInteger('work_point_id');

            /*
            |--------------------------------------------------------------------------
            | SUPPLIER / VENDOR
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('vendor_id');

            /*
            |--------------------------------------------------------------------------
            | PURCHASE ORDER DETAILS
            |--------------------------------------------------------------------------
            */
            $table->string('po_no')->unique();
            $table->string('pi_no')->nullable();
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();

            $table->enum('purchase_type', [
                'GeneralSupply',
                'RawMaterial'
            ])->default('GeneralSupply');

            /*
            |--------------------------------------------------------------------------
            | SHIPPING / FROM / TERMS
            |--------------------------------------------------------------------------
            */
            $table->text('ship_to')->nullable();
            $table->text('vendor_from')->nullable();
            $table->string('shipping_method')->nullable();
            $table->string('shipping_terms')->nullable();
            $table->string('delivery_point')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('remarks')->nullable();

            /*
            |--------------------------------------------------------------------------
            | CURRENCY / VAT
            |--------------------------------------------------------------------------
            */
            $table->string('currency')->default('USD');
            $table->decimal('exchange_rate', 18, 4)->default(1);
            $table->decimal('vat_rate', 18, 2)->default(18);

            /*
            |--------------------------------------------------------------------------
            | AMOUNT BREAKDOWN
            |--------------------------------------------------------------------------
            */
            $table->decimal('sub_total', 18, 2)->default(0);
            $table->decimal('vat_amount', 18, 2)->default(0);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('total_tzs', 18, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | PAYMENT TRACKING
            |--------------------------------------------------------------------------
            */
            $table->enum('payment_status', [
                'unpaid',
                'partial',
                'paid'
            ])->default('unpaid');

            $table->decimal('amount_paid', 18, 2)->default(0);
            $table->decimal('balance', 18, 2)->default(0);

            $table->enum('payment_method', [
                'cash',
                'pettycash',
                'bank',
                'cheque',
                'mobile'
            ])->nullable();

            $table->string('cheque_no')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_attachment')->nullable();
            $table->date('payment_date')->nullable();

            /*
            |--------------------------------------------------------------------------
            | RECEIVING TRACKING
            |--------------------------------------------------------------------------
            */
            $table->enum('receive_status', [
                'pending',
                'partial',
                'received'
            ])->default('pending');

            $table->date('received_date')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();

            /*
            |--------------------------------------------------------------------------
            | DOCUMENT ATTACHMENTS
            |--------------------------------------------------------------------------
            */
            $table->string('supplier_proforma_attachment')->nullable();
            $table->string('supplier_invoice_attachment')->nullable();
            $table->string('delivery_note_attachment')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ACCOUNTING
            |--------------------------------------------------------------------------
            */
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();
            $table->uuid('accounting_transaction_group')->nullable();

            /*
            |--------------------------------------------------------------------------
            | APPROVAL / STATUS
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'Draft',
                'Approved',
                'Ordered',
                'PartiallyReceived',
                'Received',
                'Closed',
                'Cancelled'
            ])->default('Draft');

            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AUDIT
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | FOREIGN KEYS
            |--------------------------------------------------------------------------
            */
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('business_unit_id')
                ->references('id')
                ->on('company_units')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('vendor_id')
                ->references('id')
                ->on('vendors')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('received_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */
            $table->index('po_no');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('vendor_id');
            $table->index('purchase_type');
            $table->index('payment_status');
            $table->index('receive_status');
            $table->index('status');
            $table->index('accounting_transaction_group');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}