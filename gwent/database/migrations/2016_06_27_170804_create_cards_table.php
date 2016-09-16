<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_card', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('title');
            $table->string('slug');                         //Ссылка
            $table->string('card_type');                    //Тип карты (Нейтр/Рассовая/Спец)
            $table->string('card_race');                    //Карта принадлежит рассе
            $table->string('forbidden_races');              //Не используется рассами
            $table->tinyInteger('allowed_rows')->unsigned();//Дальность карты (Ближний/дальний/сверхдальний)
            $table->integer('card_strong')->unsigned();     //Сила карты
            $table->integer('card_value')->unsigned();      //Вес карты
            $table->boolean('is_leader')->unsigned();       //Карта лидер?
            $table->string('img_url');                      //Картинка карты
            $table->text('card_actions');                   //Действия карты
            $table->text('card_groups');                   //Действия карты
            $table->smallInteger('max_quant_in_deck')->unsigned();//Макс кол-во в колоде
            $table->text('short_description');
            $table->text('full_description');
            $table->integer('price_gold')->unsigned();
            $table->integer('price_silver')->unsigned();
            $table->integer('price_only_gold')->unsigned();
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
        Schema::drop('tbl_card');
    }
}
