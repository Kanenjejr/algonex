<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliveriesTable extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // OPTIONAL SOURCE LINKS. Sales order is not required.
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('proforma_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();

            // BASIC DOCUMENT NUMBERS
            $table->string('delivery_no')->unique();
            $table->string('waybill_no')->nullable()->unique();
            $table->string('delivery_note_no')->nullable()->unique();

            // NEW DOCUMENT NUMBERS / EXPORT REFERENCE
            $table->string('customs_manifest_no')->nullable();
            $table->string('export_reference_no')->nullable();

            $table->date('delivery_date');

            // DELIVERY / WAYBILL DETAILS
            $table->string('delivery_type')->default('local');
            $table->string('tracking_no')->nullable();
            $table->string('transport_owner')->default('company'); // company / customer
            $table->string('transport_mode')->nullable();

            // NEW TRANSPORTER FIELD
            $table->string('transporter_name')->nullable();

            // DRIVER / VEHICLE
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('vehicle_no')->nullable();

            // NEW TRUCK / TRAILER FIELDS
            $table->string('truck2_registration_no')->nullable();
            $table->string('trailer_registration_no')->nullable();

            $table->string('container_no')->nullable();

            // NEW CONTAINER FIELDS
            $table->string('container2_no')->nullable();
            $table->string('container3_no')->nullable();

            // ROUTE
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();

            // DATES
            $table->date('dispatch_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // PROOF OF DELIVERY / CUSTOMER ACCEPTANCE
            $table->string('receiver_name')->nullable();
            $table->text('receiver_signature')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('customer_accepted_at')->nullable();
            $table->unsignedBigInteger('customer_accepted_by')->nullable();

            // CONTROLLED / EXPLOSIVE GOODS DETAILS
            $table->string('permit_no')->nullable();
            $table->string('storage_type')->nullable();
            $table->decimal('approved_qty', 15, 4)->nullable();

            // NEW TOTAL GROSS WEIGHT
            $table->string('total_gross_weight')->nullable();

            $table->string('safety_officer')->nullable();
            $table->string('escort_officer')->nullable();
            $table->string('authority')->nullable();

            // NEW CUSTOMS ROAD MANIFEST FIELDS
            $table->string('clearing_agent')->nullable();
            $table->string('bill_of_entry_no')->nullable();
            $table->string('exit_entry_no')->nullable();

            // WAYBILL / DELIVERY SERVICE INCOME
            $table->decimal('delivery_income_amount', 15, 4)->default(0);
            $table->string('delivery_income_currency', 10)->default('TZS');
            $table->decimal('delivery_income_exchange_rate', 18, 6)->default(1);
            $table->string('delivery_payment_method')->nullable(); // bank / cash / mobile
            $table->unsignedBigInteger('delivery_payment_account_id')->nullable();
            $table->unsignedBigInteger('delivery_service_income_account_id')->nullable();
            $table->uuid('delivery_income_transaction_group')->nullable();

            // STATUS / APPROVAL
            $table->enum('status', ['pending', 'approved', 'dispatched', 'delivered', 'closed', 'cancelled'])
                ->default('pending');

            $table->string('approval_status')->default('pending');
            $table->string('delivery_status')->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('approval_comment')->nullable();
            $table->boolean('locked')->default(false);

            // NOTES
            $table->text('notes')->nullable();

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // AUDIT
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('proforma_id')->references('id')->on('proformas')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('customer_accepted_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('delivery_payment_account_id')->references('id')->on('accnt_subcharts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('delivery_service_income_account_id')->references('id')->on('accnt_subcharts')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('company_id')->references('id')->on('company_sites')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('business_unit_id')->references('id')->on('company_units')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('work_point_id')->references('id')->on('work_points')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();

            // INDEXES
            $table->index('invoice_id');
            $table->index('proforma_id');
            $table->index('customer_id');
            $table->index('delivery_no');
            $table->index('waybill_no');
            $table->index('delivery_note_no');
            $table->index('customs_manifest_no');
            $table->index('export_reference_no');
            $table->index('delivery_date');
            $table->index('transport_owner');
            $table->index('transport_mode');
            $table->index('vehicle_no');
            $table->index('dispatch_date');
            $table->index('expected_delivery_date');
            $table->index('permit_no');
            $table->index('status');
            $table->index('approval_status');
            $table->index('delivery_status');
            $table->index('delivery_payment_account_id');
            $table->index('delivery_service_income_account_id');
            $table->index('delivery_income_transaction_group');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('created_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
}