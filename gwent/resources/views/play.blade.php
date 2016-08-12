@extends('layouts.game')
@section('content')
    @if(!empty($errors->all()))
        @foreach($errors->all() as $key => $value)
            {{ $value }}
        @endforeach
        {{ die() }}
    @endif

    <?php
    $user = Auth::user();

    $battle_members = \App\BattleMembersModel::where('battle_id','=',$battle_data->id)->get();

    $players = ['enemy' => [], 'allied' => []];
    $players_count = 0;   

    $battle_field = unserialize($battle_data->battle_field);

    if($user['id'] == $battle_data->creator_id){
        $user_field_identificator = 'p1';
        $opponent_field_identificator = 'p2';
    }else{
        $user_field_identificator = 'p2';
        $opponent_field_identificator = 'p1';
    }

    foreach($battle_members as $key => $value){
        //Создание сторон противников и союзников
        $player_data = \DB::table('users')->select('id','login','img_url')->where('id', '=', $value -> user_id)->get();
        $race_name = \DB::table('tbl_race')->select('slug', 'title')->where('slug', '=', $value -> user_deck_race)->get();

        if($user['id'] == $value->user_id){
            //dd(unserialize($value -> user_hand));
            $players['allied'] = [
                'battle_field'  => unserialize($value -> battle_field),
                'user_deck'     => unserialize($value -> user_deck),
                'user_deck_race'=> $race_name[0] -> title,
                'user_energy'   => $value -> user_energy,
                'user_hand'     => unserialize($value -> user_hand),
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
                'user_ready'    => $value -> user_ready,
            ];
        }else{
            $players['enemy'] = [
                'battle_field'  => unserialize($value -> battle_field),
                'user_deck'     => unserialize($value -> user_deck),
                'user_deck_race'=> $race_name[0] -> title,
                'user_energy'   => $value -> user_energy,
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
            ];
        }
        $players_count++;
    }
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

    <div class="convert-left-info">
        <!-- Колода и отбой противника -->
        <div class="cards-bet cards-oponent">
            <ul id="card-give-more-oponent" 
                @if(isset($players['enemy']['user_nickname']))
                    data-user="{{ $players['enemy']['user_nickname'] }}"
                @endif
            >
                <!-- Колода противника -->
                <li>
                    <div class="card-init">
                        <div class="card-otboy-counter deck">
                            <div class="counter">
                                @if(isset($players['enemy']['user_deck']))
                                    {{ count($players['enemy']['user_deck'])}}
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
                <!-- Отбой противника -->
                <li><div class="nothinh-for-swap"></div><!-- nothing-to-swap пустой контейнер отбоя/колоды --></li>
            </ul>
        </div>
        <!--END OF Колода и отбой противника -->

        <!-- Место для показа информации о карте детально-->
        <div class="card-description">
            <ul id="notSortableOne">

            </ul>
        </div>
        <!-- END OF Место для показа информации о карте детально-->

        <div class="cards-bet cards-main">
            <!-- Колода и отбой игрока-->
            <ul id="card-give-more-user" data-user="{{ $players['allied']['user_nickname'] }}">
                <li>
                    <div class="card-my-init cards-take-more">
                        <!-- Колода игрока -->
                        <div class="convert-otboy-cards">

                        </div>
                        <!-- END OF Колода игрока -->

                        <!-- Количество карт в колоде -->
                        <div class="card-take-more-counter deck">
                            <div class="counter">
                                @if(isset($players['allied']['user_deck']))
                                    {{ count($players['allied']['user_deck'])}}
                                @endif
                            </div>
                        </div>
                        <!--END OF Количество карт в колоде -->
                    </div>
                </li>
                <li>
                    <div class="nothinh-for-swap"></div><!-- Если в отбое нету карт -->
                    <!-- Если в отбое есть карты -->
                    <!--<div class="card-my-init">

                        <div class="convert-otboy-cards">
                            <ul id="otboy-cards-list">

                            </ul>
                        </div>

                        <div class="card-otboy-counter">
                            <div class="counter">Количество карт в отбое</div>
                        </div>
                    </div>-->
                    <!-- END OF Если в отбое есть карты -->
                </li>
            </ul>
            <!--END OF Колода и отбой игрока-->
        </div>

    </div>

    <!-- Поле битвы -->
    <div class="convert-battle-front">
        <!-- Поле противника -->
        <div class="convert-cards oponent" @if(isset($players['enemy']['user_nickname']))data-user="{{ $players['enemy']['user_nickname'] }}" id="{{$opponent_field_identificator}}"@endif>
            <div class="convert-card-box">
                <!-- Сверхдальние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="superRange">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$opponent_field_identificator][2]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$opponent_field_identificator][2]['special']->id}}" data-relative="{{ $battle_field[$opponent_field_identificator][2]['special']->type }}" title="{{ $battle_field[$opponent_field_identificator][2]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$opponent_field_identificator][2]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$opponent_field_identificator][2]['special']->strength }}</div>
                                </div>
                                @endif
                            </div>

                            <!-- Поле размещения сверхдальних карт -->
                            <div class="inputer-field-super-renge fields-for-cards-wrap">

                                <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого сверхдальнего ряда --></div>
                                
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$opponent_field_identificator][2]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}"  title="{{ $card->title}}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список сверхдальних карт-->
                            </div>
                            <!-- END OF Поле размещения сверхдальних карт -->
                        </div>
                    </div>
                    <div class="field-for-sum"><!-- Сумарная сила воинов в сверхдальнем ряду --></div>
                </div>
                <!-- END OF Сверхдальние Юниты противника -->

                <!-- Дальние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="range">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$opponent_field_identificator][1]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$opponent_field_identificator][1]['special']->id}}" data-relative="{{ $battle_field[$opponent_field_identificator][1]['special']->type }}"  title="{{ $battle_field[$opponent_field_identificator][1]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$opponent_field_identificator][1]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$opponent_field_identificator][1]['special']->strength }}</div>
                                </div>
                                @endif
                            </div>
                            <!-- Поле размещения дальних карт -->
                            <div class="inputer-field-range fields-for-cards-wrap">

                                <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого дальнего ряда --></div>
                                <!-- Список дальних карт-->
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$opponent_field_identificator][1]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}" title="{{ $card->title}}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список дальних карт-->
                            </div>
                            <!-- END OF Поле размещения дальних карт -->
                        </div>
                    </div>
                    <div class="field-for-sum"><!-- Сумарная сила воинов в дальнем ряду --></div>
                </div>
                <!-- END OF Дальние Юниты противника -->

                <!-- Ближние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="meele">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$opponent_field_identificator][0]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$opponent_field_identificator][0]['special']->id}}" data-relative="{{ $battle_field[$opponent_field_identificator][0]['special']->type }}" title="{{ $battle_field[$opponent_field_identificator][0]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$opponent_field_identificator][0]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$opponent_field_identificator][0]['special']->strength }}</div>
                                </div>
                                @endif
                            </div>
                            <div class="inputer-field-meele fields-for-cards-wrap">

                                <div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
                                <!-- Список ближних карт-->
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$opponent_field_identificator][0]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список ближних карт-->
                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum"><!-- Сумарная сила воинов в ближнем ряду --></div>
                </div>
                <!-- END OF Ближние Юниты противника -->
            </div>
        </div>
        <!--END OF Поле противника -->

        <div class="mezdyline"></div>

        <!-- Поле пользователя -->
        <div class="convert-cards user" data-user="{{ $players['allied']['user_nickname'] }}" id="{{$user_field_identificator}}">
            <div class="convert-card-box">
                <!-- Ближние Юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="meele">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$user_field_identificator][0]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$user_field_identificator][0]['special']->id}}" data-relative="{{ $battle_field[$user_field_identificator][0]['special']->type }}" title="{{ $battle_field[$user_field_identificator][0]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$user_field_identificator][0]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$user_field_identificator][0]['special']->strength }}</div>
                                </div>
                                @endif
                            </div><!-- Место для спецкарты -->

                            <div class="inputer-field-meele fields-for-cards-wrap">

                                <div class="bg-img-meele fields-for-cards-img"></div>

                                <!-- Список ближних карт-->
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$user_field_identificator][0]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}" title="{{ $card->title}}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список ближних карт-->
                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum"><!-- Сила воинов в ближнем ряду--></div>
                </div>
                <!-- END OF Ближние Юниты пользователя -->

                <!-- Дальние Юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="range">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$user_field_identificator][1]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$user_field_identificator][1]['special']->id}}" data-relative="{{ $battle_field[$user_field_identificator][1]['special']->type }}" title="{{ $battle_field[$user_field_identificator][1]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$user_field_identificator][1]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$user_field_identificator][1]['special']->strength }}</div>
                                </div>
                                @endif
                            </div><!-- Место для спецкарты -->

                            <div class="inputer-field-range fields-for-cards-wrap">

                                <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                <!-- Список дальних карт-->
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$user_field_identificator][1]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}" title="{{ $card->title}}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список дальних карт-->

                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum"></div>
                </div>
                <!-- END OF Дальние Юниты пользователя -->

                <!-- Сверхдальние юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-for-cards" id="superRange">

                            <div class="image-inside-line">
                                @if(!empty($battle_field[$user_field_identificator][2]['special']))
                                <div class="card-wrap" data-cardid="{{ $battle_field[$user_field_identificator][2]['special']->id}}" data-relative="{{ $battle_field[$user_field_identificator][2]['special']->type }}" title="{{ $battle_field[$user_field_identificator][2]['special']->title}}">
                                    <img src="{{ URL::asset('/img/card_images/'.$battle_field[$user_field_identificator][2]['special']->img_url) }}" alt="">
                                    <div class="label-power-card">{{ $battle_field[$user_field_identificator][2]['special']->strength }}</div>
                                </div>
                                @endif
                            </div><!-- Место для спецкарты -->

                            <div class="inputer-field-super-renge fields-for-cards-wrap">

                                <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                <!-- Список сверхдальних карт-->
                                <ul class="cards-row-wrap">
                                @foreach($battle_field[$user_field_identificator][2]['warrior'] as $i => $card)
                                    <li data-cardid="{{ $card->id}}" data-relative="{{ $card->type }}" title="{{ $card->title}}">
                                        <div class="card-wrap">
                                            <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="">
                                            <div class="label-power-card">{{ $card->strength }}</div>
                                        </div>
                                    </li>
                                @endforeach
                                </ul>
                                <!-- END OF Список сверхдальнихдальних карт-->

                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum"></div>
                </div>
                <!-- END OF Сверхдальние юниты пользователя -->
            </div>
        </div>
        <!-- END OF Поле пользователя -->

        <div class="user-card-stash">

            <div class="timer-for-play cfix">
                <div class="title-timer">ход противника:</div>
                <div class="timer-tic-tac-convert">
                    <div class="tic-tac">
                        <div class="tic-tac-wrap">
                            <span class="tic">01</span>
                            <span>:</span>
                            <span class="tac">05</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Карты руки пользователя -->
            <ul id="sortableUserCards" class="user-hand-cards-wrap cfix">
            @if($players['allied']['user_ready'] > 0)
                @foreach($players['allied']['user_hand'] as $i => $card)
                <li data-cardid="{{ $card['id'] }}" data-relative="{{ $card['type'] }}">
                    <div class="card-wrap">
                        <img src="/img/card_images/{{ $card['img_url'] }}" alt="">
                        <div class="label-power-card">{{ $card['strength'] }}</div>
                        <div class="hovered-items">
                            <div class="card-name-property"><p>{{ $card['title'] }}</div>
                        </div>
                    </div>
                </li>
                @endforeach
            @endif
            </ul>
            <!-- END OF Карты руки пользователя -->

            <div class="buttons-block-play cfix">
                <button class="button-push" name="userPassed">
                    <div class="button-pass"> <p> ПАС </p></div>
                </button>
                <button class="button-push" name="userGiveUpRound">
                    <div class="button-giveup"> <p> СДАТЬСЯ </p></div>
                </button>
            </div>

        </div>
    </div>
    <!-- TND OF Поле битвы -->
    </div>


    <!-- Правый сайдбар -->
    <div class="convert-right-info">
        <div class="oponent-describer" @if(isset($players['enemy']['user_nickname']))id="{{ $players['enemy']['user_nickname'] }}"@endif>

            <div class="useless-card">
                <div class="inside-for-some-block" style="">
                    <ul class="magic-effects-wrap">
                        <!-- Активная магия -->
                    </ul>
                </div>
            </div>

            <!-- Данные попротивника -->
            <div class="stash-about" >
                <div class="power-element">
                    <div class="power-text power-text-oponent"><!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork"
                        @if( (isset($players['enemy']['user_img']) ) && (!empty($players['enemy']['user_img'])) )
                        style="background: url('/img/user_images/{{$players['enemy']['user_img']}}') 50% 50% no-repeat;"
                        @endif
                    >

                    </div><!-- Аватар игрока -->

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
                        <div class="name">@if(isset($players['enemy']['user_nickname'])){{$players['enemy']['user_nickname']}}@endif<!-- Имя противника --></div>
                        <div class="rasa">
                        @if(isset($players['enemy']['user_deck_race']))
                            {{$players['enemy']['user_deck_race']}}
                        @endif

                        <!-- Колода противника-->
                        </div>
                    </div>
                </div>

                <div class="oponent-stats">
                    <div class="stats-power">
                        <div class="pover-greencard">
                            <img src="{{ URL::asset('images/greencard.png') }}" alt="">

                            <div class="greencard-num"><!-- Количество карт на руках --></div>
                        </div>
                    </div>
                    <div class="stats-shit">
                    @if(isset($players['enemy']['user_energy']))
                        {{$players['enemy']['user_energy']}}
                    @endif
                    <!-- Количество Энергии противника -->
                    </div>
                </div>
            </div>
        </div>

        <div class="mezhdyblock">
            <div class="bor-beutifull-box">
                <ul id="sortable-cards-field-more" class="can-i-use-useless sort">
                @foreach($battle_field['mid'] as $i => $card)
                    <li data-cardid="{{ $card[0]->id }}" data-relative="{{ $card[0]->type }}">
                        <div class="card-wrap">
                            <img src="{{ URL::asset('/img/card_images/'.$card[0]->img_url)}}" alt="">
                            <div class="label-power-card">{{ $card[0]->strength }}</div>
                        </div>
                    </li>
                @endforeach
                </ul>
            </div>
        </div>

        <!-- Данные пользователя -->
        <div class="user-describer" id="{{ $players['allied']['user_nickname'] }}">
            <div class="stash-about">
                <div class="power-element">
                    <div class="power-text  power-text-user"><!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork" @if(!empty($players['allied']['user_img']))style="background: url('/img/user_images/{{$players['allied']['user_img']}}') 50% 50% no-repeat;"@endif></div><!-- Аватар игрока -->

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
                        <div class="name">{{$players['allied']['user_nickname']}}<!-- Имя игрока --></div>
                        <div class="rasa">{{$players['allied']['user_deck_race']}}<!-- Колода игрока --></div>
                    </div>

                </div>
                <div class="user-stats">
                    <div class="stats-power">
                        <div class="pover-greencard">
                            <img src="{{ URL::asset('images/greencard.png') }}" alt="">
                            <div class="greencard-num"><!-- Количество карт на руках --></div>
                        </div>
                    </div>
                    <div class="stats-shit">{{$players['allied']['user_energy']}}<!-- Количество Энергии игрока --></div>
                </div>
            </div>
            <div class="useless-card">
                <div class="inside-for-some-block">
                    <ul class="magic-effects-wrap">
                        <!-- Активная магия -->
                    </ul>
                </div>
            </div>
        </div>

    </div>
</div>
@stop