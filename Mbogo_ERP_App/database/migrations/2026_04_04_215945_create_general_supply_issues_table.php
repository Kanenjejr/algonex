<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneralSupplyIssuesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('general_supply_issues')) {

            Schema::create('general_supply_issues', function (Blueprint $table) {
                $table->id();

                // 🔥 muhimu: nullable + same type
                $table->unsignedBigInteger('request_id')->nullable();

                $table->unsignedBigInteger('company_id')->nullable();
                $table->unsignedBigInteger('comp_unit_id')->nullable();
                $table->unsignedBigInteger('work_point_id')->nullable();
                $table->unsignedBigInteger('dept_id')->nullable();
                $table->unsignedBigInteger('section_id')->nullable();

                $table->enum('stock_scope', ['Shared', 'Dedicated'])->default('Shared');

                $table->unsignedBigInteger('item_id');
                $table->unsignedBigInteger('item_description_id');

                $table->date('issue_date');
                $table->decimal('issued_qty', 18, 2)->default(0);
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('issued_by')->nullable();

                $table->string('status')->default('Issued');
                $table->timestamps();
            });

            // 🔥 ADD FOREIGN KEYS SAFELY
            Schema::table('general_supply_issues', function (Blueprint $table) {

                if (Schema::hasTable('general_supply_requests')) {
                    $table->foreign('request_id')
                        ->references('id')
                        ->on('general_supply_requests')
                        ->onDelete('cascade');
                }

                if (Schema::hasTable('company_sites')) {
                    $table->foreign('company_id')->references('id')->on('company_sites')->cascadeOnDelete();
                }

                if (Schema::hasTable('company_units')) {
                    $table->foreign('comp_unit_id')->references('id')->on('company_units')->cascadeOnDelete();
                }

                if (Schema::hasTable('work_points')) {
                    $table->foreign('work_point_id')->references('id')->on('work_points')->cascadeOnDelete();
                }

                if (Schema::hasTable('departments')) {
                    $table->foreign('dept_id')->references('id')->on('departments')->cascadeOnDelete();
                }

                if (Schema::hasTable('sections')) {
                    $table->foreign('section_id')->references('id')->on('sections')->cascadeOnDelete();
                }

                if (Schema::hasTable('general_supply_items')) {
                    $table->foreign('item_id')->references('id')->on('general_supply_items')->cascadeOnDelete();
                }

                if (Schema::hasTable('general_supply_item_descriptions')) {
                    $table->foreign('item_description_id')
                        ->references('id')
                        ->on('general_supply_item_descriptions')
                        ->cascadeOnDelete();
                }
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('general_supply_issues');
    }
}