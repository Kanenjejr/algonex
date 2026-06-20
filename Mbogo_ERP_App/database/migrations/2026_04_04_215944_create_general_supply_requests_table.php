<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSupplyRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('general_supply_requests', function (Blueprint $table) {
            $table->id();

            // ORGANIZATION STRUCTURE
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('company_sites')
                ->cascadeOnDelete();

            $table->foreignId('comp_unit_id')
                ->nullable()
                ->constrained('company_units')
                ->cascadeOnDelete();

            $table->foreignId('work_point_id')
                ->nullable()
                ->constrained('work_points')
                ->cascadeOnDelete();

            $table->foreignId('dept_id')
                ->nullable()
                ->constrained('departments')
                ->cascadeOnDelete();

            $table->foreignId('section_id')
                ->nullable()
                ->constrained('sections')
                ->cascadeOnDelete();

            // STOCK SCOPE
            $table->enum('stock_scope', ['Shared', 'Dedicated'])->default('Shared');

            // ITEM
            $table->foreignId('item_id')
                ->nullable()
                ->constrained('general_supply_items')
                ->nullOnDelete();

            $table->foreignId('item_description_id')
                ->nullable()
                ->constrained('general_supply_item_descriptions')
                ->nullOnDelete();

            // REQUEST DETAILS
            $table->date('request_date');
            $table->string('request_no')->nullable();
            $table->decimal('requested_qty', 18, 2)->default(0);
            $table->decimal('issued_qty', 18, 2)->default(0);

            // RECEIVING / CONFIRMATION
            $table->decimal('received_qty', 18, 2)->default(0);
            $table->date('received_date')->nullable();
            $table->text('received_remarks')->nullable();

            $table->foreignId('received_confirmed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // USER INFO
            $table->text('reason')->nullable();

            $table->foreignId('requested_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // STATUS
            $table->string('status')->default('Pending');

            $table->timestamps();

            // INDEXES
            $table->index('company_id');
            $table->index('comp_unit_id');
            $table->index('work_point_id');
            $table->index('dept_id');
            $table->index('section_id');
            $table->index('stock_scope');
            $table->index('item_id');
            $table->index('item_description_id');
            $table->index('request_date');
            $table->index('request_no');
            $table->index('received_confirmed_by');
            $table->index('requested_by');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_supply_requests');
    }
}