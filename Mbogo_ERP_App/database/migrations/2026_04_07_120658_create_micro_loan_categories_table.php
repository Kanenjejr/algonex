<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroLoanCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('micro_loan_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name')->unique();
            $table->text('description')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_loan_categories');
    }
}