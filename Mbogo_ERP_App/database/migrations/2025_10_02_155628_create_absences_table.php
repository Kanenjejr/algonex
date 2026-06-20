<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->date('date');             // date of absence (or start date)
            $table->decimal('days', 8, 2)->default(1); // number of days absent
            $table->integer('calendar_days')->nullable();
            $table->decimal('paid_days', 8, 2)->nullable();
            $table->decimal('daily_rate', 18, 2)->default(0);
            $table->boolean('deduction_is_auto')->default(true);
            $table->decimal('deduction_amount', 18, 2)->default(0); // deduction money
            $table->string('reason')->nullable();
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected, Deleted
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('absences');
    }
}