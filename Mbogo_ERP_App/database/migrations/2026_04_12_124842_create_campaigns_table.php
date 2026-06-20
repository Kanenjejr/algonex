<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('name');
            $table->text('description')->nullable();

            // TYPE
            $table->string('type')->default('discount');

            // FINANCIAL
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('budget', 15, 2)->default(0);

            // TRACKING
            $table->decimal('revenue_generated', 15, 2)->default(0);
            $table->decimal('discount_given', 15, 2)->default(0);

            // TARGET
            $table->string('customer_type')->default('all');

            // TIME
            $table->date('start_date');
            $table->date('end_date');

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // SNAPSHOT CODES/NAMES
            $table->string('company_code')->nullable();
            $table->string('company_name')->nullable();

            $table->string('business_code')->nullable();
            $table->string('business_name')->nullable();

            $table->string('location_code')->nullable();
            $table->string('location_name')->nullable();

            // STATUS
            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEYS
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('business_unit_id')
                ->references('id')
                ->on('company_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('name');
            $table->index('type');
            $table->index('customer_type');
            $table->index('start_date');
            $table->index('end_date');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}