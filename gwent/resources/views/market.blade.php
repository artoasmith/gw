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

                            <div class="dragon-middle">
                                <img src="{{ URL::asset('images/dragon_glaz.png') }}" alt=""  class="glaz" />
                                <img src="{{ URL::asset('images/header_dragon_gold.png') }}" alt="" />
                            </div>

                    </div>
                    <div class="tabulate-image"></div>
                </div>

                @include('layouts.sidebar')

                <div class="content-wrap market-page" id="market">
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
                                                <option value="{{ $race['slug'] }}">{{ $race['title'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-card-field-wrap cfix">
                            <div class="market-cards">
                                <div class="market-cards-wrap">
                                    <div class="market-cards-items-wrap">

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