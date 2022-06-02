<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('from_user');
            $table->integer('to_user')->nullable();
            $table->string('to_email');
            $table->integer('file_id' , 512);
            $table->string('subject' , 50)->nullable();
            $table->string('message' , 512)->nullable();
            $table->foreign('from_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('user_files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_files');
    }
}
