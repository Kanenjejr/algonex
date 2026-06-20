<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            // USER LINK
            $table->unsignedBigInteger('user_id')->nullable();

            // COMPANY + WORK POINT
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            // CUSTOMER LINK
            $table->unsignedBigInteger('cstm_id')->nullable();

            // CONTACT DETAILS
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            // ERP AUDIT
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            // STATUS
            $table->enum('status', ['Active', 'Inactive'])->default('Active');

            // SOFT DELETE
            $table->softDeletes();

            $table->timestamps();

            // FOREIGN KEYS
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('company_id')
                ->references('id')
                ->on('company_sites')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('work_point_id')
                ->references('id')
                ->on('work_points')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('cstm_id')
                ->references('id')
                ->on('cstm_splies')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            // INDEXES
            $table->index('user_id');
            $table->index(['company_id', 'work_point_id']);
            $table->index('cstm_id');
            $table->index('email');
            $table->index('status');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}