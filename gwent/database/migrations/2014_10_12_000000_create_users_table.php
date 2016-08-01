<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('login')->unique();
            $table->string('email');
            $table->string('password');

            $table->string('nickname');
            $table->string('name');
            $table->string('birth_date',16);
            $table->string('user_gender');
            $table->text('address');
            $table->string('img_url');

            $table->tinyInteger('is_banned')->unsigned();
            $table->string('ban_time',32);

            $table->tinyInteger('user_role')->unsigned();
            $table->tinyInteger('user_online')->unsigned();
            $table->tinyInteger('user_is_playing')->unsigned();
            $table->string('user_current_deck', 16);

            $table->tinyInteger('is_activated')->unsigned();
            $table->text('activation_code');

            $table->rememberToken();
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
        Schema::drop('users');
    }
}
