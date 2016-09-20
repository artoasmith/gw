<?php
$user = Auth::user();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link rel="icon" href="{{ URL::asset('images/favicon.ico') }}" type="image/x-icon" />
    <title>DragonHeart</title>

    <!-- build:css -->

    <link rel="stylesheet" href="{{ URL::asset('css/jquery.fancybox.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.formstyler.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.ui.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.ui.datepicker.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/0_reset.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/slick-theme.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/slick.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/jquery.jscrollpane.css') }}">

    <!-- add new file here -->

    <link rel="stylesheet" href="{{ URL::asset('css/zdev_0_basic.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_2.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_2_adapt.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_4.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_4_adapt.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_5.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_5_adapt.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_6.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_6_adapt.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_10.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('css/zdev_10_adapt.css') }}">
    <!-- endbuild -->

    <script src="{{ URL::asset('js/jquery-2.min.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <!-- SCRIPTS -->

    <!-- build:js -->
    <script type="text/javascript" src="{{ URL::asset('js/device.js') }}" ></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.fancybox.pack.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.formstyler.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/maskInput.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/slick.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.mousewheel.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/jquery.jscrollpane.min.js') }}"></script>


    <script type="text/javascript" src="{{ URL::asset('js/scenario.js') }}"></script>
    <!-- endbuild -->

    <!-- <script src="@{{ URL::asset('js/validate_script.js') }}"></script>-->

    <script src='https://www.google.com/recaptcha/api.js'></script>

    <!--[if lt IE 10]>
    <link rel="stylesheet" href="https://rawgit.com/codefucker/finalReject/master/reject/reject.css" media="all" />
    <script type="text/javascript" src="https://rawgit.com/codefucker/finalReject/master/reject/reject.min.js"></script>
    <![endif]-->
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</head>
<body>
<div class="global-wrapper">
<div class="hidden-block">
    <!-- Окно покупки карт/волшебства -->
    <div class="market-buy-popup" id="buyingCardOrmagic">
        <div class="close-popup">X</div>
        <input name="_token" type="hidden" value="{{ csrf_token() }}">

        <div class="popup-content-wrap">

        </div>
    </div>

   <!-- Меню->Играть всплывающее окно -->
    <div id="choose-rase-block">
        <div class="conteiner-rase look-wrap cfix">
            <div class="afterloader">
                <img src="{{ URL::asset('images/379.gif') }}" alt="">
            </div>
            <div class="title-rase head-text"> Выберете расу</div>
            {{ Form::open(['route' => 'user-active-games', 'method' => 'POST', 'id' => 'gameForm']) }}
            <input type="hidden" name="currentRace">
            <ul>
            @foreach($races as $key => $value)
                @if($value->race_type == 'race')

                <li>
                    <div class="image-conteiner">
                        <img src="{{ URL::asset('img/card_images/'.$value->img_url) }}" alt="">
                    </div>
                    <button class="form-button button-buy-next" type="submit" name="{{ $value -> slug }}">
                        <span class="form-button-hover"></span>
                        <span class="form-button-text">{{ $value -> title }}</span>
                    </button>
                </li>

                @endif
            @endforeach
            </ul>
            {{ Form::close() }}
        </div>
    </div>

    <!-- Покупка золота -->
    <div class="market-buy-popup" id="buySomeGold">
        <div class="close-popup">X</div>

        <div class="popup-content-wrap">
            <p>Пополнение</p>
            <p>Пополнение баланса золота, золото зачисляеться автоматически после оплаты.</p>

            <input name="goldToBuy" type="number" required="required" autocomplete="off" value="0" min="0">
            <img class="resource" src="{{ URL::asset('images/header_logo_gold.png') }}" alt="">

            <span> = </span><b id="goldToUsd">0</b><span> $ </span>

            <form id="pay" name="pay" method="POST" action="https://merchant.webmoney.ru/lmi/payment.asp" accept-charset="UTF-8">
                <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="">
                <input type="hidden" name="LMI_PAYMENT_DESC" value="Тестовая покупка золота">
                <input type="hidden" name="LMI_PAYMENT_NO" value="1">
                <input type="hidden" name="LMI_PAYEE_PURSE" value="Z145179295679">
                <input type="hidden" name="LMI_SIM_MODE" value="0">
                <input type="hidden" name="id" value="@if($user){{ $user['id'] }}@endif">
                <input type="submit" value="Пополнить">
            </form>
        </div>
    </div>

    <!-- Покупка серебра -->
    <div class="market-buy-popup" id="buySomeSilver">
        <div class="close-popup">X</div>

        <div class="popup-content-wrap">
            <p>Обмен</p>
            <img class="resource" src="{{ URL::asset('images/header_logo_gold.png') }}" alt="">
            <input name="goldToSell" type="number" required="required" autocomplete="off" value="0" min="0">
            <span> = </span>
            <img class="resource" src="{{ URL::asset('images/header_logo_silver.png') }}" alt="">
            <b id="silverToBuy">0</b>
            <input type="button" name="buyingSilver" value="Обменять">
        </div>
    </div>

    <!-- Покупка Энергии-->
    <div class="market-buy-popup" id="buySomeEnergy">
        <?php
        $exchange_options = \DB::table('tbl_etc_data')->select('label_data','meta_key','meta_value')->where('label_data', '=', 'exchange_options')->get();
        $prices = [];
        foreach($exchange_options as $key => $value){
            $prices[$value->meta_key] = $value->meta_value;
        }
        ?>
        <div class="close-popup">X</div>

        <div class="popup-content-wrap">
            <p>Обмен</p>

            <div class="popup-energy-wrap cfix">
                <span>100</span>
                <div class="popup-energy-exchanges-wrap">
                    <div>
                        <img class="resource" src="{{ URL::asset('images/header_logo_gold.png') }}" alt="">
                        <span>{{ $prices['gold_to_100_energy'] }}</span>
                        <input name="gold_to_100_energy" type="button" value="Обменять">
                    </div>
                    <div>
                        <img class="resource" src="{{ URL::asset('images/header_logo_silver.png') }}" alt="">
                        <span>{{ $prices['silver_to_100_energy'] }}</span>
                        <input name="silver_to_100_energy" type="button" value="Обменять">
                    </div>
                </div>
            </div>

            <div class="popup-energy-wrap cfix">
                <span>200</span>
                <div class="popup-energy-exchanges-wrap">
                    <div>
                        <img class="resource" src="{{ URL::asset('images/header_logo_gold.png') }}" alt="">
                        <span>{{ $prices['gold_to_200_energy'] }}</span>
                        <input name="gold_to_200_energy" type="button" value="Обменять">
                    </div>
                    <div>
                        <img class="resource" src="{{ URL::asset('images/header_logo_silver.png') }}" alt="">
                        <span>{{ $prices['silver_to_200_energy'] }}</span>
                        <input name="silver_to_200_energy" type="button" value="Обменять">
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>