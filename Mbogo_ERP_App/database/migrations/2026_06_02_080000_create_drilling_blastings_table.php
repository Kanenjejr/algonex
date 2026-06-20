<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('drilling_blastings', function (Blueprint $table) {
            $table->id();
            $table->string('record_no')->unique();
            $table->date('record_date')->nullable();

            $table->foreignId('company_id')->nullable()->index();
            $table->foreignId('comp_unit_id')->nullable()->index();
            $table->foreignId('work_point_id')->nullable()->index();

            $table->string('customer_name');
            $table->string('project_site')->nullable();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();

            $table->unsignedInteger('blasts_conducted')->default(0);
            $table->unsignedInteger('total_holes_charged')->default(0);

            $table->string('explosive_type')->nullable();
            $table->decimal('explosive_qty', 15, 2)->default(0);
            $table->decimal('detonators_qty', 15, 2)->default(0);
            $table->decimal('detonating_cord_m', 15, 2)->default(0);
            $table->decimal('booster_qty', 15, 2)->default(0);

            $table->decimal('total_rock_blasted', 15, 2)->default(0);
            $table->string('rock_unit', 20)->default('BCM');

            $table->string('authorized_blaster')->nullable();
            $table->text('remarks')->nullable();

            $table->string('status', 20)->default('Active');

            $table->foreignId('created_by')->nullable()->index();
            $table->foreignId('updated_by')->nullable()->index();
            $table->timestamps();

            $table->index(['company_id', 'work_point_id', 'record_date'], 'db_company_workpoint_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drilling_blastings');
    }
};
