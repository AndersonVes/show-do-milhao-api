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
        Schema::create('player', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_match_id');
            $table->string('name');
            $table->string('color');
            $table->float('money');
            $table->integer('jumps');
            $table->integer('cards');
            $table->integer('on_question')->default(1);
            $table->timestamps();

            $table->foreign('game_match_id')->references('id')->on('match');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('player');
    }
};
