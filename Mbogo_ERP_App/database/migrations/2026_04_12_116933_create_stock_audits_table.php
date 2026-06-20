<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAuditsTable extends Migration
{
    public function up()
    {
        Schema::create('stock_audits', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->date('audit_date')->nullable();

            // GeneralSupply / RawMaterial / Product
            $table->string('audit_type')->nullable();

            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();

            // Workflow: Open -> Approved -> Closed
            $table->enum('status', ['Open', 'Approved', 'Closed'])
                ->default('Open');

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('closed_by')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('comp_unit_id')
                ->references('id')
                ->on('company_units')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('approved_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('closed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('company_id');
            $table->index('comp_unit_id');
            $table->index('work_point_id');
            $table->index('audit_date');
            $table->index('audit_type');
            $table->index('created_by');
            $table->index('approved_by');
            $table->index('closed_by');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_audits');
    }
}