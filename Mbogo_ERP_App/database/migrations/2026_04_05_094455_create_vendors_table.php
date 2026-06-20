<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorsTable extends Migration
{
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();

            // COMPANY
            // All vendors are shared under company_id = 2
            $table->unsignedBigInteger('company_id')->default(2);

            // BASIC
            $table->string('vendor_name');
            $table->string('vendor_code')->nullable()->unique();

            // CONTACT
            $table->string('phone_no')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('tin_no')->nullable();

            // ACCOUNTING
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();

            // STATUS
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            // SYSTEM
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
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
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}