@extends('layouts.default')
@section('content')
<?php
$user = Auth::user();
$errors = $errors->all();
?>

@if($user)

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

                <div class="content-wrap market-page" id="magic">
                    <div class="content-card-wrap-main">
                        <div class="content-card-top cfix">
                            <div class="market-selection">
                                <div class="selection-rase">
                                    <div class="selection-rase-wrap">
                                        <div class="selection-rase-img">
                                            <div class="selection-rase-img-wrap">
                                                <div class="select-rase-img active">
                                                    <img src="{{ URL::asset('img/card_images/'.$races[0]['img_url']) }}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                        <select class="selection-rase-select">
                                            @foreach($races as $race)
                                                @if( ($race['slug'] != 'neutrall') && ($race['slug'] != 'special') )
                                                    <option value="{{ $race['slug'] }}">{{ $race['title'] }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="content-card-field-wrap cfix">
                            <div class="market-cards">
                                <div class="market-cards-wrap">
                                    <div class="market-cards-items-wrap effect-market-wrap">
                                        <table class="main-table">
                                            <thead>
                                            <tr>

                                                <th class="no-border"></th>
                                                <th></th>
                                                <th>Название</th>
                                                <th>Описание</th>
                                                <th>затраты энергии</th>
                                                <th colspan="2">
                                                    <table>
                                                        <tr>
                                                            <th colspan="2">Цена</th>
                                                        </tr>
                                                        <tr>
                                                            <th>Золото</th>
                                                            <th>Серебро</th>
                                                        </tr>
                                                    </table>
                                                </th>
                                                <th>Статус</th>
                                                <th>Дата окончания</th>

                                            </tr>
                                            </thead>

                                            <tbody>

                                            </tbody>

                                            <!--<tr>
                                                <td class="effect-buy no-border">
                                                    <a href="#" class="button-plus"></a>
                                                </td>
                                                <td class="effect-img">
                                                    <img src="images/effect8.png" alt="" />
                                                </td>
                                                <td class="effect-title">Исцеление</td>
                                                <td class="effect-descript">Отменяет действие всех негативных эффектов специальных карт</td>
                                                <td class="energy-effect">3</td>
                                                <td class="gold-tableCell">24</td>
                                                <td class="silver-tableCell">2 000</td>
                                                <td class="market-status-wrap">
                                                    <div class="market-status"><span></span></div>
                                                </td>
                                                <td class="effect-date">-</td>
                                            </tr>-->
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
@endif

@stop
