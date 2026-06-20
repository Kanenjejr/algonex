<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebitNotesTable extends Migration
{
    public function up()
    {
        Schema::create('debit_notes', function (Blueprint $table) {

            $table->id();

            $table->string('dn_no')->unique();
            $table->date('dn_date');

            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('supplier_invoice_id')->nullable();

            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // 🔥 ACCOUNTING (VERY IMPORTANT)
            $table->string('account_code')->nullable();
            $table->string('account_name')->nullable();

            $table->decimal('total_amount', 18, 2)->default(0);
            $table->decimal('applied_amount', 18, 2)->default(0);
            $table->decimal('remaining_amount', 18, 2)->default(0);

            $table->enum('status', ['draft','issued','applied','cancelled'])->default('draft');

            $table->text('reason')->nullable();
            $table->text('remarks')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('debit_notes');
    }
}