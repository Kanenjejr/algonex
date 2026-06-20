<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('leads', function (Blueprint $table) {

    $table->id();

    $table->string('customer_name');

    $table->string('phone')->nullable();

    $table->string('email')->nullable();

    $table->string('business_type')->nullable();

    $table->string('source')->nullable();

    $table->string('status')->default('pending');

    $table->longText('description')->nullable();

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
        Schema::dropIfExists('leads');
    }
}
