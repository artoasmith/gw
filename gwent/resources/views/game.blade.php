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

            <input name="createTable" type="button" value="Создать Стол">

            @foreach($battles as $key => $value)
                {{ Form::open(['route'=>'user-in-game', 'method'=>'POST']) }}
                <p style="margin: 10px;">
                    Стол №{{ $value->id }}
                    @if($user['id'] != $value->creator_id)
                        <input name="game" type="hidden" value="{{ App\Http\Controllers\Site\SiteFunctionsController::dsCrypt(base64_encode('000'.$value->id)) }}">
                        {{ Form::submit('Присоедениться') }}
                    @else
                        (Создан Вами)
                    @endif

                    Количество игроков -> {{ $value-> players_quantity}}
                </p>
                {{ Form::close() }}
            @endforeach
        </div>
    </div>



    <div class="market-buy-popup" id="createTable">
        <div class="close-popup">X</div>

        <div class="popup-content-wrap">
            {{ Form::open(['route' => 'user-create-table', 'method' => 'POST']) }}
            <input name="league" type="hidden" value="{{ $league }}">
            <input name="deck_weight" type="hidden" value="{{ $deck_weight }}">
            {{ Form::label('players', 'Укажите колличество игроков: ') }}
            <select name="players">
                <option value="2">2</option>
                <option value="4">4</option>
                <option value="6">6</option>
                <option value="8">8</option>
            </select>
            <div style="text-align: center; padding-top: 20px;">{{ Form::submit('Ок') }}</div>
            {{ Form::close() }}
        </div>
    </div>
@endif
@stop