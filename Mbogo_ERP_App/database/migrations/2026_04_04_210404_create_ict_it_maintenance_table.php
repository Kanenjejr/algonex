<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIctItMaintenanceTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('it_maintenance')) {

            Schema::create('it_maintenance', function (Blueprint $table) {

                $table->id();

                $table->unsignedBigInteger('asset_id')->nullable();

                // ================= CORE DATA =================
                $table->enum('maintenance_type', ['preventive', 'corrective']);
                $table->longText('description');

                $table->string('technician_name');
                $table->dateTime('maintenance_date');

                // ================= STATUS =================
                $table->enum('status', [
                    'pending',
                    'in_progress',
                    'completed',
                    'cancelled'
                ])->default('pending');

                // ================= FINANCIAL =================
                $table->decimal('cost', 15, 2)->default(0);

                $table->longText('remarks')->nullable();

                $table->timestamps();
                $table->softDeletes();

            });

        }
    }

    public function down()
    {
        Schema::dropIfExists('it_maintenance');
    }
}