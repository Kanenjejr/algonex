<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asset_transactions', function (Blueprint $table) {
            $table->id();
             // a human name for the asset instance
            $table->string('asset_name');
            $table->string('asset_tag')->nullable(); // optional tag / inventory id
            $table->unsignedBigInteger('asset_category_id')->nullable();
            // financial fields
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 18, 2)->default(0);
            $table->decimal('depreciation_rate', 8, 4)->default(0); // copied from category at time of purchase
            $table->integer('useful_life_years')->nullable(); // optional override
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            // transaction_type: acquisition | disposal | revaluation
            $table->string('transaction_type')->default('acquisition');
            $table->date('transaction_date')->nullable(); // date of this transaction (for disposal/revalue)
            $table->decimal('disposal_value', 18, 2)->nullable(); // proceed from disposal or write-off
            $table->decimal('revalue_amount', 18, 2)->nullable(); // positive or negative revaluation amount
            $table->text('description')->nullable();
            // status: Active / Disposed / Deleted
            $table->string('status')->default('Active');
            // scoping
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();
            // FKs
            $table->foreign('asset_category_id')->references('id')->on('asset_categories')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('company_sites')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('work_point_id')->references('id')->on('work_points')->onDelete('cascade')->onUpdate('cascade');
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
        Schema::dropIfExists('asset_transactions');
    }
}
