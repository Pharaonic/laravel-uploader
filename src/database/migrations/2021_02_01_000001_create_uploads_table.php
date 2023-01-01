<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('hash', 350);
            $table->string('name');
            $table->string('path');
            $table->bigInteger('size');
            $table->string('extension', 20);
            $table->string('mime', 50);
            $table->string('disk')->nullable();
            
            $table->boolean('visitable')->default(false);
            $table->unsignedBigInteger('visits')->default(0);
            $table->boolean('private')->default(false);
            
            $table->unsignedBigInteger('thumbnail_id')->nullable();
            $table->unsignedBigInteger('uploader_id')->nullable();

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
        Schema::dropIfExists('uploads');
    }
}
