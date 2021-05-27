<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadPermitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upload_permits', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('upload_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permitter_id')->nullable();
            $table->timestamp('expiration')->nullable();
            
            $table->timestamps();
            
            $table->foreign('upload_id')->references('id')->on('uploads')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('upload_permits');
    }
}
