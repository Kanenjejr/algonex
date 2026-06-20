<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistic_transport_costs', function (Blueprint $table) {
            $table->id();
            $table->string('cost_no')->unique();
            $table->unsignedBigInteger('transport_order_id')->unique()->nullable()->index();
            $table->date('cost_date');
            $table->enum('vehicle_source', ['company', 'hired'])->default('company');
            $table->decimal('hire_cost', 15, 2)->default(0);
            $table->decimal('fuel_cost', 15, 2)->default(0);
            $table->decimal('driver_allowance', 15, 2)->default(0);
            $table->decimal('escort_allowance', 15, 2)->default(0);
            $table->decimal('loading_cost', 15, 2)->default(0);
            $table->decimal('other_cost', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('comp_unit_id')->nullable()->index();
            $table->unsignedBigInteger('work_point_id')->nullable()->index();
            $table->string('status', 20)->default('Active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'cost_date', 'status'], 'ltc_company_date_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistic_transport_costs');
    }
};
