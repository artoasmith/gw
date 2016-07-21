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
    $battle_status_change = \App\BattleModel::find($battle_data->id);
    $battle_members = \App\BattleMembersModel::where('battle_id','=',$battle_data->id)->get();

    $user_positions = ['enemy' => [], 'alias' => []];
    $players_count = 0;

    //Создание сторон противников и союзников
    foreach($battle_members as $key => $value){
        if($user['id'] == $value->user_id){

           $user_positions['alias'][$value -> user_id] = [
                'user_deck'     => unserialize($value -> user_deck),
                'magic_effects' => unserialize($value -> magic_effects),
                'user_energy'   => $value -> user_energy
            ];

        }else{

            $user_positions['enemy'][$value -> user_id] = [
                'user_deck'     => unserialize($value -> user_deck),
                'magic_effects' => unserialize($value -> magic_effects),
                'user_energy'   => $value -> user_energy
            ];

        }
        $players_count++;
    }

    //Если присоединившийся пользователь не является создателем стола - изменяем статус битвы
    if($user['id'] != $battle_data['creator_id']){
        if($players_count == $battle_data -> players_quantity){
            $battle_status_change -> fight_status = 1;
            $battle_status_change -> save();
            //Выбор игрока который даст фору в первом ходе (который не будет ходить)
            $user_fora_id = $battle_members[rand(0, count($battle_members) -1)] -> user_id;
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
        <div class="oponent-describer" id="164">

            <div class="useless-card">
                <div class="inside-for-some-block"><!-- Активная магия --></div>
            </div>

            <!-- Данные попротивника -->
            <div class="stash-about" >
                <div class="power-element">
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
        <div class="user-describer" id="231">

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
<!-- take in other section -->
    <script>
        var ident = {
            battleId: {{ $battle_data->id }},
            userId: {{ $user->id }},
            hash: "{{$hash}}"
        };
        {{-- start soket --}}
        var conn = new WebSocket('ws://{{ $dom }}:8080');
        var stat = false;
        conn.onopen = function (data) {
            console.log('connected');
            stat = true;
            conn.send(
                JSON.stringify(
                    {
                        action: 'join',
                        ident: ident
                    }
                )
            );
        };

        conn.onclose = function (event) {
            stat = false;

            if (event.code == 1000)
                reason = "Normal closure, meaning that the purpose for which the connection was established has been fulfilled.";
            else if(event.code == 1001)
                reason = "An endpoint is \"going away\", such as a server going down or a browser having navigated away from a page.";
            else if(event.code == 1002)
                reason = "An endpoint is terminating the connection due to a protocol error";
            else if(event.code == 1003)
                reason = "An endpoint is terminating the connection because it has received a type of data it cannot accept (e.g., an endpoint that understands only text data MAY send this if it receives a binary message).";
            else if(event.code == 1004)
                reason = "Reserved. The specific meaning might be defined in the future.";
            else if(event.code == 1005)
                reason = "No status code was actually present.";
            else if(event.code == 1006)
                reason = "The connection was closed abnormally, e.g., without sending or receiving a Close control frame";
            else if(event.code == 1007)
                reason = "An endpoint is terminating the connection because it has received data within a message that was not consistent with the type of the message (e.g., non-UTF-8 [http://tools.ietf.org/html/rfc3629] data within a text message).";
            else if(event.code == 1008)
                reason = "An endpoint is terminating the connection because it has received a message that \"violates its policy\". This reason is given either if there is no other sutible reason, or if there is a need to hide specific details about the policy.";
            else if(event.code == 1009)
                reason = "An endpoint is terminating the connection because it has received a message that is too big for it to process.";
            else if(event.code == 1010) // Note that this status code is not used by the server, because it can fail the WebSocket handshake instead.
                reason = "An endpoint (client) is terminating the connection because it has expected the server to negotiate one or more extension, but the server didn't return them in the response message of the WebSocket handshake. <br /> Specifically, the extensions that are needed are: " + event.reason;
            else if(event.code == 1011)
                reason = "A server is terminating the connection because it encountered an unexpected condition that prevented it from fulfilling the request.";
            else if(event.code == 1015)
                reason = "The connection was closed due to a failure to perform a TLS handshake (e.g., the server certificate can't be verified).";
            else
                reason = "Unknown reason";

            showPopup(reason);
        };

        conn.onerror = function (e) {
            showPopup('Socket error');
        };

        {{--On response from server--}}
        conn.onmessage = function (e) {
            var resp = JSON.parse(e.data);
            if(typeof resp.ERROR != 'undefined')
                return showPopup(resp.ERROR);

            if(typeof resp.MESSAGE != 'undefined')
                showPopup(resp.MESSAGE);

            console.log(resp);
        };

        function showPopup(ms){
            $('#buyingCardOrmagic .popup-content-wrap').html('<p>' + ms + '</p>');
            $('#buyingCardOrmagic').show(300).delay(3000).hide(400);
        }
    </script>
@stop