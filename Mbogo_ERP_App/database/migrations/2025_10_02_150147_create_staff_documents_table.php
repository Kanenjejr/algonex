<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('user_id'); // staff user id
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('work_point_id')->nullable();

            $table->string('title')->nullable();
            $table->string('file_path'); // stored path in public folder (e.g. staff_documents/xxx.pdf)
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->bigInteger('size')->nullable();
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('staff_documents');
    }
}