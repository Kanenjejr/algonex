<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
   public function up()
{
    Schema::create('activities', function (Blueprint $table) {

        $table->id();

        // 🔹 USER
        $table->unsignedBigInteger('user_id')->nullable();
        $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

        $table->unsignedBigInteger('assigned_to')->nullable();
        $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');

        // 🔹 COMPANY STRUCTURE
        $table->unsignedBigInteger('company_id')->nullable();
        $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('restrict')->onUpdate('cascade');

        $table->unsignedBigInteger('work_point_id')->nullable();
        $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('restrict')->onUpdate('cascade');

        // 🔹 CRM (KEEP - USIGUSE)
        $table->unsignedBigInteger('opportunity_id')->nullable();
        $table->unsignedBigInteger('cstm_id')->nullable();
        $table->unsignedBigInteger('cstm_order_id')->nullable();

        // NEW ERP LINKS
        $table->unsignedBigInteger('purchase_id')->nullable();
        $table->unsignedBigInteger('sales_id')->nullable();
        $table->unsignedBigInteger('stock_id')->nullable();

        // 🔹 TYPE (UPGRADE)
        $table->string('type')->nullable(); 
        // mfano:
        // call, email, meeting, requisition, purchase, stock, invoice

        $table->string('module')->nullable(); 
        // sales, purchasing, stock, finance

        // 🔹 CONTENT
        $table->string('subject')->nullable();
        $table->text('body')->nullable();

        // 🔹 STATUS
        $table->enum('status', ['Pending','Approved','Done','Cancelled','Deleted'])
              ->default('Pending');

        // 🔹 ACCOUNTING 
        $table->string('account_code')->nullable();
        $table->string('account_name')->nullable();

        // 🔹 TRACKING
        $table->dateTime('activity_date')->nullable();
        $table->dateTime('due_at')->nullable();

        $table->timestamps();

        // 🔹 INDEX
        $table->index(['opportunity_id','cstm_id','cstm_order_id']);
        $table->index(['purchase_id','sales_id','stock_id']);
    });
    }

    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
