<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerLedgersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('customer_ledgers', function (Blueprint $table) {

    $table->id();

    $table->foreignId('customer_id')->nullable();

    $table->foreignId('invoice_id')->nullable();

    $table->foreignId('payment_id')->nullable();

    $table->decimal('invoice_amount',18,2)->default(0);

    $table->decimal('paid_amount',18,2)->default(0);

    $table->decimal('balance',18,2)->default(0);

    $table->date('transaction_date')->nullable();

    $table->string('status')->default('Pending');

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
        Schema::dropIfExists('customer_ledgers');
    }
}
