<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHeslbLoansTable extends Migration
{
    public function up()
    {
        Schema::create('heslb_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->decimal('original_amount', 18, 2)->default(0);
            $table->decimal('outstanding_balance', 18, 2)->default(0);
            $table->decimal('monthly_rate', 5, 2)->default(15.00); // 15% of basic salary
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('Active'); // Active, Paid, Suspended, Deleted
            $table->text('notes')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('heslb_loans');
    }
}
