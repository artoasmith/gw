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
            <div class="content-top-wrap disable-select">
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

            {{ Form::open(['route' => 'user-create-table', 'method' => 'POST']) }}
                <input name="league" type="hidden" value="{{ $league }}">
                <input name="deck_weight" type="hidden" value="{{ $deck_weight }}">
                <input  name="players" type="hidden" value="2">
                {{ Form::submit('Создать Стол') }}
            {{ Form::close() }}

            <div class="tables-list">
                @foreach($battles as $value)
                    <p style="margin: 10px;">
                        Стол №{{ $value->id }}
                        @if($user['id'] != $value->creator_id)

                            @if($value -> fight_status == '0')
                                <a class="play-game" href="/play/{{ $value->id }}" id="{{ $value->id }}">Присоединиться</a>
                            @else
                                <a class="play-game" href="/play/{{ $value->id }}" id="{{ $value->id }}">Вернуться за стол</a>
                            @endif

                        @else
                            <a class="play-game" href="/play/{{ $value->id }}" id="{{ $value->id }}">Вернуться за стол</a>
                        @endif

                        Количество игроков за столом -> {{ (isset($battlesCount[$value->id])?$battlesCount[$value->id]:0)}}
                    </p>
                @endforeach

            </div>
        </div>
    </div>
@endif
@stop