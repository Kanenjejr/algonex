<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRawMaterialRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('raw_material_requests', function (Blueprint $table) {
            $table->id();

            // REQUEST DETAILS
            $table->string('request_no')->unique();
            $table->date('request_date');
            $table->enum('request_type', ['internal', 'external', 'stock', 'purchase'])->default('stock');

            // ERP CORE LINKS
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();

            // MATERIAL
            $table->unsignedBigInteger('raw_material_id');

            // QUANTITY DETAILS
            $table->decimal('requested_qty', 18, 2)->default(0);
            $table->decimal('issued_qty', 18, 2)->default(0);
            $table->decimal('remaining_qty', 18, 2)->default(0);
            $table->string('unit_name', 100)->nullable();
            $table->integer('no_of_bags')->nullable();
            $table->decimal('bag_size', 18, 2)->nullable();

            // ACCOUNTING DETAILS
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();

            // PURCHASE / APPROVAL
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();

            // OTHER
            $table->text('remarks')->nullable();
            $table->enum('status', ['Pending', 'Partially Issued', 'Fully Issued', 'Cancelled'])->default('Pending');

            // AUDIT
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('comp_unit_id')
                ->references('id')
                ->on('company_units')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('department_id')
                ->references('id')
                ->on('departments')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('raw_material_id')
                ->references('id')
                ->on('raw_materials')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('requested_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('request_no');
            $table->index('request_date');
            $table->index('request_type');
            $table->index('company_id');
            $table->index('comp_unit_id');
            $table->index('work_point_id');
            $table->index('department_id');
            $table->index('section_id');
            $table->index('raw_material_id');
            $table->index('purchase_id');
            $table->index('approved_by');
            $table->index('requested_by');
            $table->index('updated_by');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('raw_material_requests');
    }
}