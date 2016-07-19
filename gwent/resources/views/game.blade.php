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
                            <img src="images/dragon_glaz.png" alt=""  class="glaz" />
                            <img src="images/header_dragon_gold.png" alt="" />
                        </div>
                    </div>
                </div>
                <div class="tabulate-image"></div>
            </div>

            @include('layouts.sidebar')

            <input name="createTable" type="button" value="Создать Стол">

            @foreach($battles as $key => $value)

                <p>{{ var_dump($value) }}</p>

            @endforeach
        </div>
    </div>
@endif
@stop