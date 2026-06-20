<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCstmTxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cstm_txes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cstm_id')->nullable();
            $table->foreign('cstm_id')->references('id')->on('cstm_splies')->onDelete('cascade');

            $table->date('tx_date')->nullable();
            $table->enum('type', ['credit','debit'])->comment('credit = customer paid (reduce receivable) or supplier credit? interpret per business rules');
            $table->decimal('amount', 18, 4)->default(0);
            $table->decimal('balance_after', 18, 4)->nullable();
            $table->string('reference')->nullable(); // e.g., order id or invoice no
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('company_id')->nullable();
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');

            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');

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
        Schema::dropIfExists('cstm_txes');
    }
}
