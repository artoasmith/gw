<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableForEtcData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tbl_etc_data', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('label_data');
            $table->text('meta_key');
            $table->text('meta_key_title');
            $table->text('meta_value');
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
        Schema::drop('tbl_etc_data');
    }
}
