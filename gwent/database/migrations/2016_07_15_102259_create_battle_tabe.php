<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBattleTabe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_battles', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('creator_id')->unsigned();
            $table->text('players_decks'); //массив array[отбой => [], рука => [], колода => [] ]
            $table->integer('deck_weight')->unsigned();
            $table->string('league');
            $table->tinyInteger('fight_status')->unsigned(); //0 - ожидание другого игрока. 1 - подготовка к бою. 2 - бой продолжается. 3 - бой окончен
            $table->tinyInteger('player_num_turn')->unsigned();
            $table->text('round_status'); //массив array[p1 => 'количество выграных раундов', p2 => 'количество выграных раундов']
            $table->text('fight_log');
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
        Schema::drop('tbl_battles');
    }
}