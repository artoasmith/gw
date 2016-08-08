@extends('layouts.game')
@section('content')
    @if(!empty($errors->all()))
        @foreach($errors->all() as $key => $value)
                {{ $value }}
        @endforeach
        {{ die() }}
    @endif

    <?php

    function getCard($card_id){
        if(!empty($card_id)){
            $card = \DB::table('tbl_card')->select('id','title','card_type','card_strong','img_url')->where('id', '=', $card_id)->get();
            return $card[0];            
        }else{
            return false;
        }
    }
    
    function createFieldCardView($card_id){
        $cardData = getCard($card_id);
        if($cardData !== false){
            return '<li class="content-card-item" data-cardid="'.$cardData->id.'" data-relative="'.$cardData->card_type.'">'.
                createCardDescriptionView($card_id).
            '</li>';
        }        
    }
            
    function createCardDescriptionView($card_id){        
        $cardData = getCard($card_id);        
        if($cardData !== false){
            return
            '<div class="content-card-item-main" style="background-image: url(/img/card_images/'.$cardData->img_url.')">'.
                '<div class="label-power-card">'.$cardData->card_strong.'</div>'.
                '<div class="hovered-items">'.
                    '<div class="card-name-property"><p>'.$cardData->title.'</div>'.
                '</div>'.
            '</div>';
        }        
    }
    /*
     * Для готовой модели
     * Нужно зашифровать все поля:
     *      $players['enemy'][0]['user_nickname']
     *      $card[id]
     *      $magic_effect[id]
    */


    $user = Auth::user();

    $battle_members = \App\BattleMembersModel::where('battle_id','=',$battle_data->id)->get();

    $players = ['enemy' => [], 'allied' => []];
    $players_count = 0;


    foreach($battle_members as $key => $value){        
        //Создание сторон противников и союзников
        $player_data = \DB::table('users')->select('id','login','img_url')->where('id', '=', $value -> user_id)->get();
        $race_name = \DB::table('tbl_race')->select('slug', 'title')->where('slug', '=', $value -> user_deck_race)->get();


        if($user['id'] == $value->user_id){
            $players['allied'][] = [
                'user_id'       => $value -> user_id,
                'user_deck'     => unserialize($value -> user_deck),
                'user_hand'     => unserialize($value -> user_hand),
                'magic_effects' => unserialize($value -> magic_effects),
                'user_energy'   => $value -> user_energy,
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
                'user_deck_race'=> $race_name[0] -> title,
                'user_ready'    => $value -> user_ready,
                'battle_field'  => unserialize($value -> battle_field)
            ];
        }else{
            $players['enemy'][] = [
                'user_id'       => $value -> user_id,
                'user_deck'     => unserialize($value -> user_deck),
                'magic_effects' => unserialize($value -> magic_effects),
                'user_energy'   => $value -> user_energy,
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
                'user_deck_race'=> $race_name[0] -> title,
                'battle_field'  => unserialize($value -> battle_field)
            ];
        }
        $players_count++;        
    }

    //Если присоединившийся пользователь не является создателем стола - изменяем статус битвы
    if($user['id'] != $battle_data['creator_id']){
        if($players_count == $battle_data -> players_quantity){
            $battle_status_change = \App\BattleModel::find($battle_data->id);
            $battle_status_change -> fight_status = 1;
            $battle_status_change -> save();
        }
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
            <ul id="card-give-more-oponent" @if(isset($players['enemy'][0]))data-user="{{ $players['enemy'][0]['user_nickname'] }}"@endif>
                <!-- Колода противника -->
                <li>
                    <div class="card-init">
                        <div class="card-otboy-counter deck">
                            <div class="counter"><!-- Колличество карт в колоде противника --></div>
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
            <ul id="card-give-more-user" data-user="{{ $players['allied'][0]['user_nickname'] }}">
                <li>
                    <div class="card-my-init cards-take-more">
                        <!-- Колода игрока -->
                        <div class="convert-otboy-cards">

                        </div>
                        <!-- END OF Колода игрока -->

                        <!-- Количество карт в колоде -->
                        <div class="card-take-more-counter deck">
                            <div class="counter"></div>
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
        <div class="convert-cards oponent" @if(isset($players['enemy'][0]))data-user="{{ $players['enemy'][0]['user_nickname'] }}"@endif>
            <div class="convert-card-box">
                <!-- Сверхдальние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-super-renge field-for-cards">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['enemy'][0]['battle_field'][2]['special']) !!}</div><!-- Место для спецкарты -->

                            <!-- Поле размещения сверхдальних карт -->
                            <div class="inputer-field-super-renge fields-for-cards-wrap">

                                <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого сверхдальнего ряда --></div>
                                <!-- Список сверхдальних карт-->
                                <ul id="sortable-oponent-cards-field-super-renge" class="can-i-use-useless sort">
                                    @foreach($players['enemy'][0]['battle_field'][2]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                    <!--<li class="content-card-item" data-relative="special" data-power='10' data-cardid="555">
                                        <div class="content-card-item-main">Бекграунд карты

                                            <div class="label-power-card">10Сила карты</div>
                                            <div class="hovered-items">

                                                <div class="card-game-status">
                                                    <img src="images/kard-property.png" alt="" />
                                                    <img src="images/kard-property.png" alt="" />
                                                    <img src="images/kard-property.png" alt="" />
                                                </div>

                                                <div class="card-name-property">
                                                    <p>Дионис Стальной Название карты</p>
                                                </div>
                                                <div class="block-describe">
                                                    <div class="block-image-describe"></div>
                                                    <div class="block-text-describe">
                                                        Описание карты (short_description)
                                                        <p>Признай свои ошибки и похорони их как следует. Иначе они придут за тобой sdfg sdf gsdf gsdf hgsdhf </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>-->

                                </ul>
                                <!-- END OF Список сверхдальних карт-->
                            </div>
                            <!-- END OF Поле размещения сверхдальних карт -->
                        </div>
                    </div>
                    <div class="field-for-sum">10<!-- Сумарная сила воинов в сверхдальнем ряду --></div>
                </div>
                <!-- END OF Сверхдальние Юниты противника -->

                <!-- Дальние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-range field-for-cards">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['enemy'][0]['battle_field'][1]['special']) !!}</div><!-- Место для спецкарты -->

                            <!-- Поле размещения дальних карт -->
                            <div class="inputer-field-range fields-for-cards-wrap">

                                <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого дальнего ряда --></div>
                                <!-- Список дальних карт-->
                                <ul id="sortable-oponent-cards-field-range" class="can-i-use-useless sort">
                                    @foreach($players['enemy'][0]['battle_field'][1]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                </ul>
                                <!-- END OF Список дальних карт-->
                            </div>
                            <!-- END OF Поле размещения дальних карт -->
                        </div>
                    </div>
                    <div class="field-for-sum">0<!-- Сумарная сила воинов в дальнем ряду --></div>
                </div>
                <!-- END OF Дальние Юниты противника -->

                <!-- Ближние Юниты противника -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-meele field-for-cards">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['enemy'][0]['battle_field'][0]['special']) !!}</div><!-- Место для спецкарты -->

                            <div class="inputer-field-meele fields-for-cards-wrap">

                                <div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
                                <!-- Список ближних карт-->
                                <ul id="sortable-oponent-cards-field-meele" class="can-i-use-useless sort">
                                    @foreach($players['enemy'][0]['battle_field'][0]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                </ul>
                                <!-- END OF Список ближних карт-->
                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum">0<!-- Сумарная сила воинов в ближнем ряду --></div>
                </div>
                <!-- END OF Ближние Юниты противника -->
            </div>
        </div>
        <!--END OF Поле противника -->

        <div class="mezdyline"></div>

        <!-- Поле пользователя -->
        <div class="convert-cards user" data-user="{{ $players['allied'][0]['user_nickname'] }}">
            <div class="convert-card-box">
                <!-- Ближние Юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-meele field-for-cards" data-fieldtype="meele">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['allied'][0]['battle_field'][0]['special']) !!}</div><!-- Место для спецкарты -->

                            <div class="inputer-field-meele fields-for-cards-wrap">

                                <div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                <!-- Список ближних карт-->
                                <ul id="sortable-user-cards-field-meele" class="can-i-use-useless sort">
                                    @foreach($players['allied'][0]['battle_field'][0]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                </ul>
                                <!-- END OF Список ближних карт-->
                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum field-for-sum-user-meele">0<!-- Сила воинов в ближнем ряду--></div>
                </div>
                <!-- END OF Ближние Юниты пользователя -->

                <!-- Дальние Юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-range field-for-cards"  data-fieldtype="range">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['allied'][0]['battle_field'][1]['special']) !!}</div><!-- Место для спецкарты -->

                            <div class="inputer-field-range fields-for-cards-wrap">

                                <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                <!-- Список дальних карт-->
                                <ul id="sortable-user-cards-field-range" class="can-i-use-useless sort">
                                    @foreach($players['allied'][0]['battle_field'][1]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                </ul>
                                <!-- END OF Список дальних карт-->

                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum field-for-sum-user-renge">0</div>
                </div>
                <!-- END OF Дальние Юниты пользователя -->

                <!-- Сверхдальние юниты пользователя -->
                <div class="convert-stuff">
                    <div class="convert-one-field">
                        <div class="field-super-renge field-for-cards" data-fieldtype="super-renge">

                            <div class="image-inside-line">{!! createCardDescriptionView($players['allied'][0]['battle_field'][2]['special']) !!}</div><!-- Место для спецкарты -->

                            <div class="inputer-field-super-renge fields-for-cards-wrap">

                                <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                <!-- Список сверхдальних карт-->
                                <ul id="sortable-user-cards-field-super-renge" class="can-i-use-useless sort">
                                    @foreach($players['allied'][0]['battle_field'][2]['warrior'] as $key => $value)
                                        {!! createFieldCardView($value) !!}
                                    @endforeach
                                </ul>
                                <!-- END OF Список сверхдальнихдальних карт-->

                            </div>
                        </div>
                    </div>
                    <div class="field-for-sum field-for-sum-user-super-renge">0</div>
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
                @if($players['allied'][0]['user_ready'])
                    @foreach($players['allied'][0]['user_hand'] as $i => $card_data)
                        <li data-cardid="{{ $card_data['id'] }}" data-relative="{{ $card_data['type'] }}">
                            <img title="'{{ $card_data['title'] }}" alt="{{ $card_data['slug'] }}" src="/img/card_images/{{ $card_data['img_url'] }}">
                            <div class="card-strength-wrap">{{ $card_data['strength'] }}</div>
                            <div class="card-name-property"><p>{{ $card_data['title'] }}</p></div>
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
        <div class="oponent-describer" @if(isset($players['enemy'][0]))id="{{ $players['enemy'][0]['user_nickname'] }}"@endif>

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
                    <div class="power-text power-text-oponent">0<!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork"
                        @if( (isset($players['enemy'][0]) ) && (!empty($players['enemy'][0]['user_img'])) )
                        style="background: url('/img/user_images/{{$players['enemy'][0]['user_img']}}') 50% 50% no-repeat;"
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
                        <div class="name">@if(isset($players['enemy'][0])){{$players['enemy'][0]['user_nickname']}}@endif<!-- Имя противника --></div>
                        <div class="rasa">
                        @if(isset($players['enemy'][0]))
                            {{$players['enemy'][0]['user_deck_race']}}
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
                    @if(isset($players['enemy'][0]))
                        {{$players['enemy'][0]['user_energy']}}
                    @endif

                    <!-- Количество Энергии противника -->
                    </div>
                </div>
            </div>
        </div>

        <div class="mezhdyblock">
            <div class="bor-beutifull-box">
                <ul id="sortable-cards-field-more" class="can-i-use-useless sort"></ul>
            </div>
        </div>

        <!-- Данные пользователя -->
        <div class="user-describer" id="{{ $players['allied'][0]['user_nickname'] }}">
            <div class="stash-about">
                <div class="power-element">
                    <div class="power-text  power-text-user">1<!-- Сумарная сила воинов во всех рядах противника --></div>
                </div>
                <div class="oponent-discribe">

                    <div class="image-oponent-ork" @if(!empty($players['allied'][0]['user_img']))style="background: url('/img/user_images/{{$players['allied'][0]['user_img']}}') 50% 50% no-repeat;"@endif></div><!-- Аватар игрока -->

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
                        <div class="name">{{$players['allied'][0]['user_nickname']}}<!-- Имя игрока --></div>
                        <div class="rasa">{{$players['allied'][0]['user_deck_race']}}<!-- Колода игрока --></div>
                    </div>

                </div>
                <div class="user-stats">
                    <div class="stats-power">
                        <div class="pover-greencard">
                            <img src="{{ URL::asset('images/greencard.png') }}" alt="">
                            <div class="greencard-num"><!-- Количество карт на руках --></div>
                        </div>
                    </div>
                    <div class="stats-shit">{{$players['allied'][0]['user_energy']}}<!-- Количество Энергии игрока --></div>
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