<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSwHwIssuesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('software_hardware_issues')) {

            Schema::create('software_hardware_issues', function (Blueprint $table) {

                //  IMPORTANT (FK stability)
                $table->engine = 'InnoDB';

                $table->id();
                $table->unsignedBigInteger('asset_id')->nullable();

                // ================= DEVICE =================
                $table->string('device_name');

                // ================= ISSUE TYPE =================
                $table->enum('issue_type', ['software', 'hardware']);

                $table->string('category');
                $table->longText('problem_description');

                // ================= PRIORITY =================
                $table->enum('priority_level', [
                    'low',
                    'medium',
                    'high',
                    'critical'
                ])->default('medium');

                // ================= DATE =================
                $table->dateTime('date_reported');

                // ================= ASSIGNED TECHNICIAN =================
                $table->foreignId('assigned_to')
                      ->nullable()
                      ->constrained('users')
                      ->nullOnDelete();

                // ================= STATUS =================
                $table->enum('issue_status', [
                    'open',
                    'pending',
                    'in_progress',
                    'resolved',
                    'closed'
                ])->default('open');

                // ================= RESOLUTION =================
                $table->longText('resolution_details')->nullable();
                $table->dateTime('resolved_date')->nullable();

                // ================= FINANCIAL LINK =================
                $table->decimal('estimated_cost', 15, 2)->default(0);
                $table->decimal('actual_cost', 15, 2)->default(0);

                $table->timestamps();
                $table->softDeletes();

            });

        }
    }

    public function down()
    {
        if (Schema::hasTable('software_hardware_issues')) {
            Schema::dropIfExists('software_hardware_issues');
        }
    }
}