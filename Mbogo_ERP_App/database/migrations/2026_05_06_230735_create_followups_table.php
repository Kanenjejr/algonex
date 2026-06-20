<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFollowupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('followups', function (Blueprint $table) {

    $table->id();

    $table->foreignId('customer_id')->nullable();

    $table->date('followup_date')->nullable();

    $table->string('priority')->default('Medium');

    $table->longText('notes')->nullable();

    $table->string('status')->default('Pending');

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
        Schema::dropIfExists('followups');
    }
}
