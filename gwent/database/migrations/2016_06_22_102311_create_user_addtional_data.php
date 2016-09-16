<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserAddtionalData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_user_data', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('login');
            $table->string('email');
            $table->string('user_base_race');
            $table->string('user_gold');
            $table->string('user_silver');
            $table->string('user_energy');
            $table->text('user_available_deck');
            $table->text('user_cards_in_deck');
            $table->text('user_magic_effects');
            $table->string('user_level');
            $table->string('user_rating');
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
        Schema::drop('tbl_user_data');
    }
}