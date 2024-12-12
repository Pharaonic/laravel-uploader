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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thumbnail_id')->nullable()->constrained('uploads')->nullOnDelete();

            $table->string('hash', 350)->index();
            $table->string('disk')->nullable();
            $table->string('visibility')->nullable();

            $table->string('name');
            $table->string('path');
            $table->unsignedBigInteger('size');
            $table->string('extension', 25);
            $table->string('mime', 70);

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
};
