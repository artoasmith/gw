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
            $table->text('user_deck');
            $table->text('magic_effects');
            $table->integer('user_energy')->unsigned();
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
