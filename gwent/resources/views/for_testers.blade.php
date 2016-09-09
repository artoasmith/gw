@extends('layouts.default')
@section('content')

<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if(isset($user))

    @include('layouts.top')

    <div class="main">
        <div class="mbox">
            <div class="content-top-wrap">
                <div class="dragon-image cfix">
                    <div class="dragon-middle-wrap">
                        <div class="dragon-middle">
                            <img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
                            <img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
                        </div>
                    </div>
                </div>
                <div class="tabulate-image"></div>
            </div>

            @include('layouts.sidebar')
            
            <div class="content-wrap training-page">
                <div class="ctext">
                    <p>Для начала игры необходимо наполнить колоду. При наполнении колоды высчитывается <i>сила колоды</i>, а из неё уже высчитывается <i>лига</i></p>
                    <p>Список лиг относительно силы колоды:</p>
                    <ul>
                        <li>1я - от 0 до 75</li>
                        <li>2я - от 76 до 125</li>
                        <li>3я - от 126 до 200</li>
                        <li>4я - от 201 до 300</li>
                        <li>5я - от 301</li>
                    </ul>
                    <p>Бот для игры еще не написан, и, для того чтобы поиграть с кем-то, нужно найти себе противника IRL или создать себе еще одного пользователя и играть в два окна.</p>
                    <p><b>Внимание!</b> Проблемой <i>"я-нимагу-присаидиница-к-сталу"</i> является разница между лигами игроков</p>
                    <br><br>
                    <p>Список последних изменений:</p>
                    <ul>
                        <li>05.09.2016
                            <ol>
                                <li>Сделаны все действия специальных карт и карт воинов</li>
                            </ol>
                        </li>
                        <li>07.09.2016
                            <ol>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Растеряность</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Воодушевление</b>(3ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Хозяева леса" &mdash; <b>Страх</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Хозяева леса" &mdash; <b>Сила духа</b>(2ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Горцы" &mdash; <b>Страх</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Горцы" &mdash; <b>Испуг</b>(2ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Горцы" &mdash; <b>Опытный техник</b>(5ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Проклятые" &mdash; <b>Слабость</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Нечисть" &mdash; <b>Растеряность</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Нечисть" &mdash; <b>Печаль</b>(2ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Монстры" &mdash; <b>Слабость</b>(1ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Монстры" &mdash; <b>Перегруппировка</b>(2ур)</li>
                            </ol>
                        </li>
                        <li>08.09.2016
                            <ol>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Вера</b>(2ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Молитва</b>(4ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Бог войны</b>(5ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Рыцари Империи" &mdash; <b>Благославление</b>(6ур)</li>
                                <li><ins>Доделаны все волшебные умения фракции "Рыцари Империи"</ins></li>
                                <li>Сделано <i>Волшебство</i> фракции "Хозяева леса" &mdash; <b>Боевой клич</b>(5ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Горцы" &mdash; <b>Призыв к оружию</b>(3ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Горцы" &mdash; <b>Точность</b>(4ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Проклятые" &mdash; <b>Ослабление</b>(3ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Нечисть" &mdash; <b>Проклятие</b>(4ур)</li>
                                <li>Сделано <i>Волшебство</i> фракции "Монстры" &mdash; <b>Жажда крови</b>(5ур)</li>
                            </ol>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
@stop