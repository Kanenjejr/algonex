<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistic_fleet_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code')->unique();
            $table->string('plate_number')->unique();
            $table->string('vehicle_type');
            $table->enum('ownership', ['company', 'hired'])->default('company');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->unsignedSmallInteger('manufacture_year')->nullable();
            $table->string('fuel_type')->nullable();
            $table->decimal('fuel_rate_per_liter', 15, 2)->nullable();
            $table->decimal('hire_rate_per_day', 15, 2)->nullable();
            $table->decimal('capacity', 10, 2)->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('comp_unit_id')->nullable()->index();
            $table->unsignedBigInteger('work_point_id')->nullable()->index();
            $table->string('status', 20)->default('Active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'comp_unit_id', 'work_point_id'], 'lfv_company_unit_wp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistic_fleet_vehicles');
    }
};
