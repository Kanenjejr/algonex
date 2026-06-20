<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanProductsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_products', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('comp_unit_id')->nullable();
            $table->foreign('comp_unit_id')->references('id')->on('company_units')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('loan_category_id');
            $table->foreign('loan_category_id')->references('id')->on('micro_loan_categories')->onDelete('restrict')->onUpdate('cascade');

            $table->string('product_name');
            $table->decimal('min_amount', 18, 2)->default(0);
            $table->decimal('max_amount', 18, 2)->default(0);
            $table->integer('min_duration_months')->default(1);
            $table->integer('max_duration_months')->default(1);
            $table->decimal('default_interest_rate', 8, 2)->default(0);
            $table->enum('interest_method', ['flat', 'reducing'])->default('flat');
            $table->decimal('default_penalty_percent_per_day', 8, 2)->default(0);
            $table->enum('default_penalty_basis', ['full_loan', 'remaining_balance'])->default('remaining_balance');
            $table->decimal('default_reminder_charge', 18, 2)->default(0);
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_products');
    }
}