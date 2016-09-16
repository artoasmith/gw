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
            $table->tinyInteger('players_quantity')->unsigned(); //Колличество игроков
            $table->integer('deck_weight')->unsigned();
            $table->string('league');
            $table->tinyInteger('fight_status')->unsigned(); //0 - ожидание другого игрока. 1 - подготовка к бою. 2 - бой продолжается. 3 - бой окончен
            $table->integer('user_id_turn')->unsigned(); //ID Последнего походившего пользователя (не того который сейчас ходит)
            $table->tinyInteger('round_count')->unsigned();//Текущий раунд
            $table->text('round_status'); //массив array[p1 => 'количество выграных раундов', p2 => 'количество выграных раундов']
            $table->text('battle_field');
            $table->text('undead_cards');//массив отыгравших карт с действием "Бессмертный"
            $table->text('magic_usage');//массив отыгравших магических эффектов
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
