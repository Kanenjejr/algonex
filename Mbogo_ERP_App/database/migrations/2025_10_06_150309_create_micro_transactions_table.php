<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMicroTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('micro_transactions', function (Blueprint $table) {
            $table->id();
             // required ids
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            // link to bank_network if applicable
            $table->unsignedBigInteger('bank_network_id')->nullable();
            // tx categories: Kutoa, Kuweka, Kuuza (Sell), Kununua (Buy)
            $table->enum('tx_group',['Withdraw','Deposit','FX-Sell','FX-Buy']);
            $table->string('currency', 10)->default('TZS');
            $table->decimal('amount',20,2);
            $table->decimal('fx_rate', 18,6)->nullable(); // if FX
            $table->decimal('commission',20,2)->default(0);
            $table->json('meta')->nullable(); // extra: client name, ref no ...
            $table->enum('status',['Pending','Completed','Cancelled'])->default('Completed');
            $table->index(['company_id','work_point_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('bank_network_id')->references('id')->on('bank_networks')->onDelete('set null')->onUpdate('cascade');
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
        Schema::dropIfExists('micro_transactions');
    }
}
