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

	$players = ['enemy' => [], 'alias' => []];
	$players_count = 0;


	foreach($battle_members as $key => $value){
		//Создание сторон противников и союзников
        $player_data = \DB::table('users')->select('id','login','img_url')->where('id', '=', $value -> user_id)->get();
        $race_name = \DB::table('tbl_race')->select('slug', 'title')->where('slug', '=', $value -> user_deck_race)->get();


		if($user['id'] == $value->user_id){
			$players['allied'][] = [
				'user_id'       => $value -> user_id,
				'user_deck'     => unserialize($value -> user_deck),
				'magic_effects' => unserialize($value -> magic_effects),
				'user_energy'   => $value -> user_energy,
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
                'user_deck_race'=> $race_name[0] -> title
			];
		}else{
			$players['enemy'][] = [
				'user_id'       => $value -> user_id,
				'user_deck'     => unserialize($value -> user_deck),
				'magic_effects' => unserialize($value -> magic_effects),
				'user_energy'   => $value -> user_energy,
                'user_img'      => $player_data[0] -> img_url,
                'user_nickname' => $player_data[0] -> login,
                'user_deck_race'=> $race_name[0] -> title
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
                <ul id="card-give-more-oponent">
                    <li>
                        <div class="card-init">
                            <div class="card-otboy-counter">
                                <div class="counter">23<!-- Колличество карт в колоде противника --></div>
                            </div>
                        </div>
                    </li>
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
                <ul id="card-give-more-user">
                    <li>
                        <div class="card-my-init cards-take-more">
                            <!-- Колода игрока -->
                            <div class="convert-otboy-cards">

                            </div>
                            <!-- END OF Отбой игрока -->

                            <!-- Количество карт в колоде -->
                            <div class="card-take-more-counter">
                                <div class="counter">24</div>
                            </div>
                            <!--END OF Количество карт в колоде -->
                        </div>
                    </li>
                    <li>
                        <!-- <div class="nothinh-for-swap"></div> --><!-- Если в отбое нету карт -->
                        <!-- Если в отбое есть карты -->
                        <div class="card-my-init">

                            <div class="convert-otboy-cards">
                                <ul id="otboy-cards-list">
                                    <li class="content-card-item" data-relative="special" data-power='10' data-cart-id="555">
                                        <div class="content-card-item-main"><!-- Контейнер с бэкграундом карты -->
                                            <div class="label-power-card">10</div><!-- Сила карты -->
                                            <div class="hovered-items">
                                                <!-- картинки с действиями (кажись не нужно)
                                                <div class="card-game-status">
                                                    <img src="/images/kard-property.png" alt="" />
                                                    <img src="/images/kard-property.png" alt="" />
                                                    <img src="/images/kard-property.png" alt="" />
                                                </div>
                                                -->
                                                <div class="card-name-property">
                                                    <p>Дионис Стальной <!-- Название карты (title) --></p>
                                                </div>
                                                <div class="block-describe">
                                                    <div class="block-image-describe"></div>
                                                    <div class="block-text-describe">
                                                        <!-- Описание карты (short_description) -->
                                                        <p>Признай свои ошибки и похорони их как следует. Иначе они придут за тобой sdfg sdf gsdf gsdf hgsdhf </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-otboy-counter">
                                <div class="counter">1<!-- Количество карт в отбое --></div>
                            </div>
                        </div>
                        <!-- END OF Если в отбое есть карты -->
                    </li>
                </ul>
                <!--END OF Колода и отбой игрока-->
            </div>

        </div>

        <!-- Поле битвы -->
        <div class="convert-battle-front">
            <!-- Поле противника -->
            <div class="convert-cards oponent">
                <div class="convert-card-box">
                    <!-- Сверхдальние Юниты противника -->
                    <div class="convert-stuff">
                        <div class="convert-one-field">
                            <div class="field-super-renge field-for-cards">

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <!-- Поле размещения сверхдальних карт -->
                                <div class="inputer-field-super-renge fields-for-cards-wrap">

                                    <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого сверхдальнего ряда --></div>
                                    <!-- Список сверхдальних карт-->
                                    <ul id="sortable-oponent-cards-field-super-renge" class="can-i-use-useless sort">

                                        <li class="content-card-item" data-relative="special" data-power='10' data-cart-id="555">
                                            <div class="content-card-item-main"><!-- Бекграунд карты -->

                                                <div class="label-power-card">10<!-- Сила карты --></div>
                                                <div class="hovered-items">
                                                    <!--
                                                    <div class="card-game-status">
                                                        <img src="images/kard-property.png" alt="" />
                                                        <img src="images/kard-property.png" alt="" />
                                                        <img src="images/kard-property.png" alt="" />
                                                    </div>
                                                    -->
                                                    <div class="card-name-property">
                                                        <p>Дионис Стальной <!-- Название карты --></p>
                                                    </div>
                                                    <div class="block-describe">
                                                        <div class="block-image-describe"></div>
                                                        <div class="block-text-describe">
                                                            <!-- Описание карты (short_description) -->
                                                            <p>Признай свои ошибки и похорони их как следует. Иначе они придут за тобой sdfg sdf gsdf gsdf hgsdhf </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>

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

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <!-- Поле размещения дальних карт -->
                                <div class="inputer-field-range fields-for-cards-wrap">

                                    <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого дальнего ряда --></div>
                                    <!-- Список дальних карт-->
                                    <ul id="sortable-oponent-cards-field-range" class="can-i-use-useless sort">

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

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <div class="inputer-field-meele fields-for-cards-wrap">

                                    <div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>
                                    <!-- Список ближних карт-->
                                    <ul id="sortable-oponent-cards-field-meele" class="can-i-use-useless sort">

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
            <div class="convert-cards user">
                <div class="convert-card-box">
                    <!-- Ближние Юниты пользователя -->
                    <div class="convert-stuff">
                        <div class="convert-one-field">
                            <div class="field-meele field-for-cards" data-fieldtype="meele">

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <div class="inputer-field-meele fields-for-cards-wrap">

                                    <div class="bg-img-meele fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                    <!-- Список ближних карт-->
                                    <ul id="sortable-user-cards-field-meele" class="can-i-use-useless sort">
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

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <div class="inputer-field-range fields-for-cards-wrap">

                                    <div class="bg-img-range fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                    <!-- Список дальних карт-->
                                    <ul id="sortable-user-cards-field-range" class="can-i-use-useless sort">
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

                                <div class="image-inside-line"></div><!-- Место для спецкарты -->

                                <div class="inputer-field-super-renge fields-for-cards-wrap">

                                    <div class="bg-img-super-renge fields-for-cards-img"><!-- Картинка пустого ближнего ряда --></div>

                                    <!-- Список сверхдальних карт-->
                                    <ul id="sortable-user-cards-field-super-renge" class="can-i-use-useless sort">
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
                <ul id="sortable-user-cards" class="can-i-use-useless sort">
                    <li class="content-card-item">
                        <!-- Контейнер карты -->
                        <div class="content-card-item-main card-load-info"><!-- Бекграунд карты -->

                            <div class="label-power-card">
                                <span class="label-power-card-wrap">
                                    <span>8</span><!-- Показатель силы карты -->
                                </span>
                            </div>

                            <div class="hovered-items">
                                <!--
                                <div class="card-game-status">

                                    <div class="card-game-status-role">
                                        <span class="bow"></span>
                                    </div>

                                    <div class="card-game-status-wrap">

                                        <span class="hand"></span>
                                        <span class="wind"></span>
                                        <span class="heal"></span>

                                    </div>
                                </div>
                                -->
                                <div class="card-name-property">
                                    <p><!-- Имя карты (title) --></p>
                                </div>
                                <!--<div class="block-describe">
                                    <div class="block-image-describe">
                                        <img src="/images/content-center-img.png" alt="" />
                                    </div>
                                    <div class="block-text-describe">
                                        <div class="block-text-describe-wrap">
                                            <div class="block-text-describe-main">
                                                <div class="block-text-describe-main-wrap">
                                                    Описанае карты(short_description)
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>-->
                            </div>
                        </div>
                        <!-- END OF Контейнер карты -->
                    </li>
                </ul>
                <!-- END OF Карты руки пользователя -->

                <div class="buttons-block-play cfix">
                    <a href="#" class="button-push">
                        <div class="button-pass"> <p> ПАС </p></div>
                    </a>
                    <a href="#" class="button-push">
                        <div class="button-giveup"> <p> СДАТЬСЯ </p></div>
                    </a>
                </div>

            </div>
        </div>
        <!-- TND OF Поле битвы -->
	</div>


	<!-- Правый сайдбар -->
	<div class="convert-right-info">
		<div class="oponent-describer" id="@if(isset($players['enemy'][0])){{ $players['enemy'][0]['user_id'] }}@endif">

			<div class="useless-card">
				<div class="inside-for-some-block" style=""><!-- Активная магия --></div>
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
		<div class="user-describer" id="{{ $players['allied'][0]['user_id'] }}">

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

        var timeleft = 0; //количество секунд до окончания хода, передавать в таймер (обновляется с battleTimer)
        var timedeley = {{$timeOut}}*1000;
        var timer = false;

        function battleTimer(time) { //обновление значения таймера в ходе боя
            if(typeof timer != "boolean")
                clearTimeout(timer);
            timeleft = time;

            time = time*1000;
            timer = setTimeout(timeOutCheck, time);
        }

        function timeOutCheck(){ //запускается когда заканчивается время хода, для проверки даннных на сервере
            conn.send(
                JSON.stringify(
                    {
                        action: 'checkBattle',
                        ident: ident
                    }
                )
            );
        }

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
            var action = 'none';
            if(typeof resp.action != 'undefined')
                action = resp.action;

            //battle info logic
            if(typeof resp.battleInfo != 'undefined'){
                console.log('battle info logic');
                switch (resp.battleInfo.fightStatus){
                    case 0: //логика ожидание других играков

                        break;
                    case 1: //логика подготовки к бою

                        break;
                    case 2: //логика хода боя
                        if(typeof resp.battleInfo.endTime == 'number' && resp.battleInfo.endTime>0) //обновления таймеров хода
                            battleTimer(resp.battleInfo.endTime);

                        break;
                    case 3: //логика окончаного боя

                        break
                }
            }
        };

        function showPopup(ms){
            $('#buyingCardOrmagic .popup-content-wrap').html('<p>' + ms + '</p>');
            $('#buyingCardOrmagic').show(300).delay(3000).hide(400);
        }
    </script>
@stop