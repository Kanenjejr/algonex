<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignResponsesTable extends Migration
{
    public function up()
    {
        Schema::create('campaign_responses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketing_campaign_id')->nullable();
            $table->foreign('marketing_campaign_id')->references('id')->on('marketing_campaigns')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('cstm_id')->nullable();
            $table->foreign('cstm_id')->references('id')->on('cstm_splies')->onDelete('set null');

            $table->unsignedBigInteger('contact_id')->nullable();
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');

            $table->string('response_type')->nullable(); // Interested, Not Interested, Bounce
            $table->text('notes')->nullable();
            $table->date('response_date')->nullable();
            $table->enum('status', ['New','Processed','Deleted'])->default('New');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_responses');
    }
}
