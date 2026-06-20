<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->string('account_code')->unique();
        $table->string('account_name');
        $table->string('type')->nullable(); // asset, expense, income
        $table->boolean('is_active')->default(1);
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
        Schema::dropIfExists('accounts');
    }
}
