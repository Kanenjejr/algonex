<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsReceiptsTable extends Migration
{
 public function up()
{
    Schema::create('goods_receipts', function (Blueprint $table) {

        $table->id();

        // BASIC
        $table->string('grn_no')->unique();
        $table->date('grn_date');

        //  RELATION
        $table->unsignedBigInteger('purchase_order_id');

        //  COMPANY
        $table->unsignedBigInteger('company_id')->nullable();
        $table->unsignedBigInteger('work_point_id')->nullable();

        //  STATUS
        $table->enum('status', ['draft','received','posted'])->default('draft');

        //  ACCOUNTING
        $table->string('account_code')->nullable();
        $table->string('account_name')->nullable();

        //  SYSTEM
        $table->unsignedBigInteger('received_by')->nullable();

        $table->timestamps();

        // FK
        $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
        $table->foreign('company_id')->references('id')->on('company_sites')->nullOnDelete();
        $table->foreign('work_point_id')->references('id')->on('work_points')->nullOnDelete();
        $table->foreign('received_by')->references('id')->on('users')->nullOnDelete();
    });
}
    public function down()
    {
        Schema::dropIfExists('goods_receipts');
    }
}
