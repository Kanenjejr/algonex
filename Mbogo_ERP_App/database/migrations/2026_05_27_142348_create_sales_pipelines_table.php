<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesPipelinesTable extends Migration
{
    public function up()
    {
        Schema::create('sales_pipelines', function (Blueprint $table) {
            $table->id();

            // BASIC
            $table->string('pipeline_code')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            // LINKS
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('opportunity_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();

            // COMPANY STRUCTURE
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('business_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // PIPELINE DETAILS
            $table->enum('stage', [
                'Lead',
                'Opportunity',
                'Proposal',
                'Negotiation',
                'Invoice',
                'Payment',
                'Won',
                'Lost',
                'On Hold'
            ])->default('Lead');

            $table->enum('status', [
                'Open',
                'In Progress',
                'Completed',
                'Cancelled',
                'Lost'
            ])->default('Open');

            $table->decimal('expected_value', 15, 2)->default(0);
            $table->decimal('actual_value', 15, 2)->default(0);
            $table->integer('probability')->default(0);

            $table->date('expected_close_date')->nullable();
            $table->date('closed_date')->nullable();

            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEYS
            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('lead_id')
                ->references('id')
                ->on('leads')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('opportunity_id')
                ->references('id')
                ->on('opportunities')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // Use these only if invoices/payments tables already exist before this migration.
            // If they fail during migrate:fresh, remove these two FK blocks and keep columns only.
            /*
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            */

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

            $table->foreign('assigned_to')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
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

            // INDEXES
            $table->index('pipeline_code');
            $table->index('stage');
            $table->index('status');
            $table->index('customer_id');
            $table->index('lead_id');
            $table->index('opportunity_id');
            $table->index('company_id');
            $table->index('business_unit_id');
            $table->index('work_point_id');
            $table->index('assigned_to');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_pipelines');
    }
}