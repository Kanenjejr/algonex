<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditNotesTable extends Migration
{
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {

            $table->id();

            // BASIC
            $table->string('credit_note_no')->unique();
            $table->date('date');

            // RELATIONS
            $table->foreignId('invoice_id')
                  ->constrained('invoices')
                  ->cascadeOnDelete();

            $table->foreignId('customer_id')
                  ->constrained('customers')
                  ->cascadeOnDelete();

            // AMOUNT
            $table->decimal('amount', 15, 2);

            // REASON
            $table->text('reason')->nullable();

            // STATUS
            $table->enum('status', ['draft','approved','applied'])
                  ->default('draft');

            // SYSTEM
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('company_id')->references('id')->on('company_sites')->nullOnDelete();
            $table->foreign('work_point_id')->references('id')->on('work_points')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

        });
    }

    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
}
