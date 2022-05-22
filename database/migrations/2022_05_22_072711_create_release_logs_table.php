<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReleaseLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('release_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->dateTime('release_date');
            $table->string('version' , 20);
            $table->longText('release_log');
            $table->string('issue_link' , 1024);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('release_logs');
    }
}
