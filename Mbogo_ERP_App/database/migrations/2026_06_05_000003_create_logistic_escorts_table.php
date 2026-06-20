<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logistic_escorts', function (Blueprint $table) {
            $table->id();
            $table->string('escort_code')->unique();
            $table->string('full_name');
            $table->string('phone')->nullable();
            $table->decimal('allowance_rate', 15, 2)->nullable();
            $table->unsignedBigInteger('company_id')->nullable()->index();
            $table->unsignedBigInteger('comp_unit_id')->nullable()->index();
            $table->unsignedBigInteger('work_point_id')->nullable()->index();
            $table->string('status', 20)->default('Active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->index(['company_id', 'comp_unit_id', 'work_point_id'], 'le_company_unit_wp_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logistic_escorts');
    }
};
