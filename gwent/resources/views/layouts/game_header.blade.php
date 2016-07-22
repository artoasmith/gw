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
    <script type="text/javascript" src="{{ URL::asset('js/game.js') }}"></script>
    <script type="text/javascript" src="{{ URL::asset('js/cron_imitator.js') }}"></script>
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
<div class="hidden-block">
    <!-- Окно покупки карт/волшебства -->
    <div class="market-buy-popup" id="buyingCardOrmagic">
        <div class="close-popup">X</div>
        <input name="_token" type="hidden" value="{{ csrf_token() }}">

        <div class="popup-content-wrap">

        </div>
    </div>
</div>