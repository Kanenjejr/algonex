<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // BASIC INFO
            $table->string('customer_code')->unique();
            $table->string('customer_name');
            $table->string('customer_type')->default('company');
            $table->text('description')->nullable();

            // ACCOUNTING LINK - linked to accnt_subcharts
            $table->unsignedBigInteger('account_id')->nullable();

            // CONTACT INFO
            $table->string('phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();

            // BUSINESS INFO
            $table->string('tin_number')->nullable();
            $table->string('vrn')->nullable();

            // COUNTRY INFO
            $table->string('country', 10)->nullable();
            $table->string('destination', 10)->nullable();

            // ACCOUNTING
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('opening_balance', 15, 2)->default(0);

            // SYSTEM
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // STATUS
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            $table->timestamps();
            $table->softDeletes();

            // FOREIGN KEYS
            $table->foreign('account_id')
                ->references('id')
                ->on('accnt_subcharts')
                ->nullOnDelete()
                ->cascadeOnUpdate();

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

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('customer_code');
            $table->index('customer_name');
            $table->index('customer_type');
            $table->index('account_id');
            $table->index('email');
            $table->index('phone');
            $table->index('tin_number');
            $table->index('country');
            $table->index('destination');
            $table->index('company_id');
            $table->index('comp_unit_id');
            $table->index('work_point_id');
            $table->index(['company_id', 'comp_unit_id', 'work_point_id']);
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('status');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
}