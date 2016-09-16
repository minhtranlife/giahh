<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDmhhtn55Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dmhhtn55', function (Blueprint $table) {
            $table->increments('id');
            $table->string('masopnhom');
            $table->string('mahh');
            $table->string('masp');
            $table->string('tenhh');
            $table->string('dacdiemkt');
            $table->string('dvt');
            $table->string('nsx');
            $table->string('gc');
            $table->string('thoidiem');
            $table->string('sapxep');
            $table->string('theodoi');
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
        Schema::drop('dmhhtn55');
    }
}
