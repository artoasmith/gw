@extends('layouts.game')
@section('content')
<?php
$user = Auth::user();
/*
$players_decks = unserialize($battle_data->players_decks);
$players_decks[] = [$user['id'] => $user['user_current_deck']];

$battle_data -> players_decks = serialize($players_decks);
$battle_data -> save();

*/
?>
<header class="header">
    <div class="mbox">
        <div class="header-box cfix">
            <div class="convert-header">
                <div class="user preload">
                    <div class="preloader">
                        <img src="{{ URL::asset('images/359.gif') }}" alt="">
                    </div>
                    <div class="user-image"></div>
                    <div class="user-name">{{ $user['login'] }}</div>
                </div>
                <div class="convert-stats">
                    <div class="stats ">
                        <div class="time-box">
                            <img src="{{ URL::asset('images/header_logo_time.png') }}" alt="" />
                            <div class="time"> 04:36:22 </div>
                        </div>
                        <div class="people-box preload">
                            <div class="preload-peoples">
                                <img src="{{ URL::asset('images/379.gif') }}" alt="">
                            </div>
                            <img src="{{ URL::asset('images/header_logo_man.png') }}" alt="" />
                            <div class="people"></div>
                        </div>
                    </div>
                </div>
                <div class="rating">
                    <p>РЕЙТИНГ</p>
                    <div class="convert-resurses preload">
                        <div class="preload-resurses">
                            <img src="{{ URL::asset('images/76.gif') }}" alt="">
                        </div>

                        <div class="resurses">
                            <a href="#buy-gold" class="button-plus buy-more-gold"></a>
                            <img src="{{ URL::asset('images/header_logo_gold.png') }}" alt="" />

                            <div class="gold"></div>
                        </div>
                        <div class="resurses">
                            <a href="#buy-silver" class="button-plus buy-more-silver"></a>
                            <img src="{{ URL::asset('images/header_logo_silver.png') }}" alt="" />
                            <div class="silver"></div>
                        </div>
                        <div class="resurses ">
                            <a href="#buy-energy" class="button-plus buy-more-energy"></a>
                            <img src="{{ URL::asset('images/header_logo_lighting.png') }}" alt="" />
                            <div class="lighting"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="wrap-play">
    <div class="content-top-wrap">
        <div class="dragon-image cfix">
            <div class="dragon-middle-wrap">
                <div class="dragon-middle">
                    <img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
                    <img src="{{ URL::asset('images/dragoon-small.png') }}" alt="" class="dragoon"  />
                </div>
            </div>
        </div>
        <div class="tabulate-image"></div>
    </div>




    <div class="field-battle">

    </div>


    <!-- Правый сайдбар -->
    <div class="convert-right-info">
        <div class="oponent-describer">

            <div class="useless-card">
                <div class="inside-for-some-block"><!-- Активная магия --></div>
            </div>

            <!-- Данные попротивника -->
            <div class="stash-about">
                <div class="power-element ">
                    <div class="power-text power-text-oponent">0<!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork"><!-- Аватар противника --></div>

                    <!-- Количество выиграных раундов (скорее всего) n из 3х -->
                    <div class="circle-status" data-pct="25">

                        <svg id="svg" width='140px'  viewPort="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <filter id="MyFilter" filterUnits="userSpaceOnUse" x="0" y="0" width="200" height="200">
                                <feGaussianBlur in="SourceAlpha" stdDeviation="4" result="blur"/>
                                <feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
                                <feSpecularLighting in="blur" surfaceScale="5" specularConstant=".75" specularExponent="20" lighting-color="#bbbbbb" result="specOut">
                                    <fePointLight x="-5000" y="-10000" z="20000"/>
                                </feSpecularLighting>
                                <feComposite in="specOut" in2="SourceAlpha" operator="in" result="specOut"/>
                                <feComposite in="SourceGraphic" in2="specOut" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litPaint"/>
                                <feMerge>
                                    <feMergeNode in="offsetBlur"/>
                                    <feMergeNode in="litPaint"/>
                                </feMerge>
                            </filter>
                            <circle filter="url(#MyFilter)" id="bar-oponent" r="65" cx="71" cy="71" fill="transparent" stroke-dasharray="409" stroke-dashoffset="100px" stroke-linecap="round"></circle>
                        </svg>

                    </div>

                    <div class="naming-oponent">
                        <div class="name">Боднарио Дионис<!-- Имя противника --></div>
                        <div class="rasa">Чудовище<!-- Колода противника--></div>
                    </div>
                </div>

                <div class="oponent-stats">
                    <div class="stats-power">
                        <div class="pover-greencard">
                            <img src="{{ URL::asset('images/greencard.png') }}" alt="">

                            <div class="greencard-num"> 10<!-- Количество карт на руках --></div>
                        </div>
                    </div>
                    <div class="stats-shit">13<!-- Количество Энергии противника --></div>
                </div>
            </div>
        </div>

        <div class="mezhdyblock">
            <div class="bor-beutifull-box">
                <ul id="sortable-cards-field-more" class="can-i-use-useless sort">
                    <li class="content-card-item" data-relative="special" data-power="<!--Сила карты-->" data-cart-id="ID карты в base64">
                        <div class="content-card-item-main">
                            <div class="label-power-card"><!--Сила карты--></div>
                            <div class="hovered-items">
                                <!-- Кажись не нужно
                                <div class="card-game-status">
                                    <img src="images/kard-property.png" alt="" />
                                    <img src="images/kard-property.png" alt="" />
                                    <img src="images/kard-property.png" alt="" />
                                </div>
                                -->

                                <!-- Блоки ниже срабатывают при наведении -->
                                <div class="card-name-property">
                                    <p><!-- Список названий действий --></p>
                                </div>
                                <div class="block-describe">
                                    <div class="block-image-describe"></div>
                                    <div class="block-text-describe">
                                        <p><!-- Список описаний действий --></p>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </li>
                </ul>

            </div>
        </div>

        <!-- Данные пользователя -->
        <div class="user-describer">

            <div class="stash-about">
                <div class="power-element">
                    <div class="power-text  power-text-user">1<!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork"><!-- Аватар игрока --></div>

                    <div class="circle-status">
                        <svg id="svg" width='140px'  viewPort="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
                            <filter id="MyFilter" filterUnits="userSpaceOnUse" x="0" y="0" width="200" height="200">
                                <feGaussianBlur in="SourceAlpha" stdDeviation="4" result="blur"/>
                                <feOffset in="blur" dx="4" dy="4" result="offsetBlur"/>
                                <feSpecularLighting in="blur" surfaceScale="5" specularConstant=".75" specularExponent="20" lighting-color="#bbbbbb" result="specOut">
                                    <fePointLight x="-5000" y="-10000" z="20000"/>
                                </feSpecularLighting>
                                <feComposite in="specOut" in2="SourceAlpha" operator="in" result="specOut"/>
                                <feComposite in="SourceGraphic" in2="specOut" operator="arithmetic" k1="0" k2="1" k3="1" k4="0" result="litPaint"/>
                                <feMerge>
                                    <feMergeNode in="offsetBlur"/>
                                    <feMergeNode in="litPaint"/>
                                </feMerge>
                            </filter>
                            <circle filter="url(#MyFilter)" id="bar-user" r="65" cx="71" cy="71" fill="transparent" stroke-dasharray="409" stroke-dashoffset="100px" stroke-linecap="round"></circle>
                        </svg>
                    </div>

                    <div class="naming-user">
                        <div class="name">Боднарио Дионис<!-- Имя игрока --></div>
                        <div class="rasa">Чудовище<!-- Колода игрока --></div>
                    </div>
                </div>
                <div class="user-stats">
                    <div class="stats-power">
                        <div class="pover-greencard">
                            <img src="{{ URL::asset('images/greencard.png') }}" alt="">
                            <div class="greencard-num"> 10<!-- Количество карт на руках --></div>
                        </div>
                    </div>
                    <div class="stats-shit"><!-- Количество Энергии игрока --></div>
                </div>
            </div>
        </div>
        <div class="useless-card">
            <div class="inside-for-some-block"><!-- Активная магия --></div>
        </div>
    </div>
</div>
@stop