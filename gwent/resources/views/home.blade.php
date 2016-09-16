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
            <div class="main_page cfix rase">
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

                <div class="content-wrap">
                    <!-- add many img must do programist -->
                    <div class="rase">
                        <img src="{{ URL::asset('images/main_bg_1.jpg') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>


@else


    <div class="main form-block">
        <div class="mbox login-page">

            <div class="forms-header"></div>

            <div class="convert-about">
                <div class="contein">
                    <div class="title">
                        <h2>об игре</h2>
                    </div>
                    <div class="ctext">
                        <p>Сайт рыбатекст поможет дизайнеру, верстальщику, вебмастеру сгенерировать несколько абзацев более менее осмысленного текста рыбы на русском языке, а начинающему оратору отточить навык публичных выступлений в домашних условиях. При создании генератора мы использовали небезызвестный универсальный код речей. Текст генерируется абзацами случайным образом от двух до десяти предложений в абзаце, что позволяет сделать текст более привлекательным и живым для визуально-слухового восприятия.</p>
                        <p>По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых клиентов недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен. Сайт рыбатекст поможет дизайнеру, верстальщику, вебмастеру сгенерировать несколько абзацев более менее осмысленного текста рыбы на русском языке, а начинающему оратору отточить навык публичных выступлений в домашних условиях. При создании генератора мы использовали небезызвестный универсальный код речей. Текст генерируется абзацами случайным образом от двух до десяти предложений в абзаце, что позволяет сделать текст более привлекательным и живым для визуально-слухового восприятия.</p>
                        <p> По своей сути рыбатекст является альтернативой традиционному lorem ipsum, который вызывает у некторых клиентов недоумение при попытках прочитать рыбу текст. В отличии от lorem ipsum, текст рыба на русском языке наполнит любой макет непонятным смыслом и придаст неповторимый колорит советских времен.</p>
                    </div>
                </div>
            </div>
            <div class="button-dropdown">
                <a href="" class="drop-menu-open button-buy-next">
                    <span class="form-button-hover"></span>
                    <span class="form-button-text"> ОБ ИГРЕ </span>
                </a>
            </div>

            <div class="forms-main vfix">

                <div class="conteiner-title">
                    <div class="geib-text">
                        <img src="{{ URL::asset('images/gayb-say.png') }}" alt="">
                    </div>
                </div>

                <div class="conteiner-form">
                    <div class="form-wrap">
                        <div class="form-wrap-main">
                            <div class="form-description">Войдите или зарегистрируйтесь</div>
                            <div class="form-wrap-item placeholder-form">

                                {{ Form::open(['route' => 'user-login', 'class' => 'forget-pass-form', 'method' => 'POST', 'autocomplete' => 'off']) }}

                                <input type="hidden" name="action" value="user_login" class="typesubmit" />
                                <div class="form-wrap-row submit">
                                    <div class="form-wrap-value">
                                        <div class="form-wrap-input">
                                            <button class="button-buy-next @if(empty($errors)) show-form-please @endif" type="submit">
                                                <span class="form-button-hover"></span>
                                                <span class="form-button-text">Вход</span>
                                            </button>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-wrap-for-rows" @if(!empty($errors)) style="display: block;" @endif>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <p>Логин</p>
                                                <input type="text" name="login" required="required"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <p>Пароль</p>
                                                <input type="password" name="password" required="required"  />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-links cfix">
                                        <div class="form-links-item right">
                                            <a href="forget_pass.html">Забыли пароль ?</a>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row error-text" @if(!empty($errors)) style="display: block;" @endif>
                                        @if(!empty($errors))
                                            @foreach($errors as $error)
                                                <p>{!! $error !!}</p>
                                            @endforeach
                                        @endif
                                    </div>

                                </div>
                                {{ Form::close() }}
                            </div>



                            <a href="{{ route('user-registration') }}" class="button-buy-next">
                                <span class="form-button-hover"></span>
                                <span class="form-button-text">Регистрация</span>
                            </a>
                            <!--
                            <div class="forms-language">
                                <div class="form-language-wrap">
                                    <a href="#">
                                        <img src="images/ua-flag.png" alt="" />
                                    </a>
                                </div>
                                <div class="form-language-wrap active">
                                    <a href="#">
                                        <img src="images/rus-flag.png" alt="" />
                                    </a>
                                </div>
                                <div class="form-language-wrap">
                                    <a href="#">
                                        <img src="images/uk-flag.png" alt="" />
                                    </a>
                                </div>
                            </div>
                            -->
                        </div>
                    </div>
                </div>

                <div class="row-with-rase">
                    <div class="conteiner">

                        @foreach($races as $race)
                            <div class="item-rise">
                                <a href="" class="rase-ric">
                                    <img src="{{ URL::asset('img/card_images/'.$race->img_url) }}" alt="">
                                    <span>{{ $race->title }}</span>
                                </a>
                                <div class="hovered-block">
                                    <div class="contein">
                                        <div class="top-img">
                                            <img src="{{ URL::asset('img/card_images/'.$race->img_url) }}" alt="">
                                        </div>
                                        <div class="description">
                                            <div class="title-rase">{{ $race->description_title }}</div>
                                            <div class="des-text">
                                                {!! $race->description !!}
                                            </div>
                                        </div>
                                        <a href="{{ route('user-registration', ['fraction' => $race->slug]) }}" class="button-easy">
                                            <span class="form-button-text"> ИГРАТЬ</span>
                                            <span class="form-button-hover"></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@stop