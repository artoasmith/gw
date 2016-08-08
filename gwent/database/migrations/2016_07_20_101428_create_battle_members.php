<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBattleMembers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_battle_members', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->integer('battle_id')->unsigned();
            $table->string('user_deck_race');
            $table->text('user_deck');
            $table->text('user_hand');
            $table->text('magic_effects');
            $table->integer('user_energy')->unsigned();
            $table->tinyInteger('user_ready')->unsigned();
            $table->tinyInteger('round_passed')->unsigned();
            $table->tinyInteger('rounds_won')->unsigned();
            $table->text('battle_field');
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
        Schema::drop('tbl_battle_members');
    }
}
