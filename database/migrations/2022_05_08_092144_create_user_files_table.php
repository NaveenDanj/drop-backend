<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->nullable();
            $table->string('fileID' , 512);
            $table->string('original_name' , 512);
            $table->string('name' , 512);
            $table->string('extension' , 32);
            $table->boolean('isPasswordProtected')->default(false);
            $table->string('password' , 512)->nullable();
            $table->boolean('isDayExpired')->default(false);
            $table->bigInteger('expired_at')->nullable();
            $table->boolean('isDownloadExpired')->default(false);
            $table->integer('download_count')->default(0)->nullable();
            $table->integer('download_expired_at')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_files');
    }
};
