<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommunicationsTable extends Migration
{
    public function up()
    {
        Schema::create('communications', function (Blueprint $table) {

            $table->id();

            $table->foreignId('customer_id')->nullable();

            $table->string('type')->nullable();

            $table->string('subject')->nullable();

            $table->longText('message')->nullable();

            $table->string('status')->default('Sent');

            $table->foreignId('user_id')->nullable();

            $table->foreignId('company_id')->nullable();

            $table->foreignId('work_point_id')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('communications');
    }
}