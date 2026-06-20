<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePipelinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('pipelines', function (Blueprint $table) {

    $table->id();

    $table->foreignId('customer_id')->nullable();

    $table->foreignId('lead_id')->nullable();

    $table->foreignId('opportunity_id')->nullable();

    $table->string('stage')->default('lead');

    $table->decimal('expected_value',18,2)->default(0);

    $table->integer('probability')->default(0);

    $table->date('expected_close_date')->nullable();

    $table->string('status')->default('active');

    $table->longText('remarks')->nullable();

    $table->foreignId('user_id')->nullable();

    $table->foreignId('company_id')->nullable();

    $table->foreignId('work_point_id')->nullable();

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
        Schema::dropIfExists('pipelines');
    }
}
