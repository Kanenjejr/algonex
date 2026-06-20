<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistic_transport_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->date('order_date');
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('comp_unit_id')->nullable()->index();
            $table->unsignedBigInteger('work_point_id')->nullable()->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->string('customer_name');
            $table->string('cargo_description');
            $table->string('origin');
            $table->string('destination');
            $table->enum('vehicle_source', ['company', 'hired'])->default('company');
            $table->unsignedBigInteger('company_vehicle_id')->nullable()->index();
            $table->string('hired_vehicle_name')->nullable();
            $table->string('hired_vehicle_plate')->nullable();
            $table->decimal('hired_vehicle_cost', 15, 2)->default(0);
            $table->unsignedBigInteger('driver_id')->nullable()->index();
            $table->string('escort_name')->nullable();
            $table->decimal('escort_allowance', 15, 2)->default(0);
            $table->decimal('driver_allowance', 15, 2)->default(0);
            $table->decimal('expected_fuel_liters', 15, 2)->default(0);
            $table->decimal('fuel_rate', 15, 2)->default(0);
            $table->decimal('revenue_amount', 15, 2)->default(0);
            $table->string('status', 20)->default('Draft');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'order_date', 'status'], 'lto_company_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistic_transport_orders');
    }
};
