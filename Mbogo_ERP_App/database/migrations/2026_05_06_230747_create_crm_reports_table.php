<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCrmReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_reports', function (Blueprint $table) {

            $table->id();

            $table->string('report_name');

            $table->string('report_type');

            $table->date('start_date')->nullable();

            $table->date('end_date')->nullable();

            $table->integer('total_customers')->default(0);

            $table->integer('total_leads')->default(0);

            $table->decimal('total_sales',18,2)->default(0);

            $table->decimal('total_payments',18,2)->default(0);

            $table->decimal('total_debts',18,2)->default(0);

            $table->integer('total_opportunities')->default(0);

            $table->integer('total_campaigns')->default(0);

            $table->foreignId('generated_by')->nullable();

            $table->foreignId('company_id')->nullable();

            $table->foreignId('work_point_id')->nullable();

            $table->longText('remarks')->nullable();

            $table->string('status')->default('generated');

            $table->softDeletes();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_reports');
    }
}