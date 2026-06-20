<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanCollateralsTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_collaterals', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('loan_application_id');
            $table->foreign('loan_application_id')->references('id')->on('micro_loan_applications')->onDelete('cascade')->onUpdate('cascade');

            $table->enum('collateral_type', ['BusinessShare', 'Asset', 'Vehicle', 'Plot', 'House', 'LogBook', 'Other'])->default('Other');
            $table->string('item_name');
            $table->integer('no_of_items')->default(1);
            $table->string('serial_number')->nullable();
            $table->string('color')->nullable();
            $table->decimal('original_cost', 18, 2)->default(0);
            $table->decimal('estimated_value', 18, 2)->default(0);
            $table->decimal('discounted_value', 18, 2)->default(0);
            $table->text('ownership_notes')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_collaterals');
    }
}