<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpportunitiesTable extends Migration
{
    public function up()
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();

            $table->string('opportunity_name')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->unsignedBigInteger('assigned_to')->nullable();

            // COMPANY STRUCTURE
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // CUSTOMER
            $table->unsignedBigInteger('cstm_id')->nullable();

            // FINANCIAL
            $table->decimal('estimated_value', 15, 2)->nullable();

            $table->date('close_expected')->nullable();

            $table->enum('stage', [
                'Prospecting',
                'Qualification',
                'Proposal',
                'Negotiation',
                'Closed Won',
                'Closed Lost',
                'On Hold'
            ])->default('Prospecting');

            $table->enum('status', [
                'Open',
                'Won',
                'Lost',
                'Deleted'
            ])->default('Open');

            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEYS
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

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

            $table->foreign('cstm_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('opportunity_name');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('cstm_id');
            $table->index('stage');
            $table->index('status');
            $table->index('assigned_to');
            $table->index('deleted_at');
        });
    }
    public function down()
    {
        Schema::dropIfExists('opportunities');
    }
}