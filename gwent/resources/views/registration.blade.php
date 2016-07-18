@extends('layouts.default')
@section('content')
<?php
$errors = $errors->all();
?>
    <div class="not-main">
        <div class="main form-block">

            <div class="mbox">

                <div class="forms-header"></div>

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

                <div class="forms-main vfix">
                    <div class="forms-img">
                        <img src="images/gwent-forms-img-fon.png" alt="" />
                    </div>
                    <div class="form-wrap">
                        <div class="form-wrap-main">
                            @if(isset($user) && ($user))

                            <div class="form-title">Вы уже зарегистрированы</div>

                            @else

                            <div class="form-title">РЕГИСТРАЦИЯ</div>

                            <div class="form-wrap-item placeholder-form">
                                {{ Form::open(['route' => 'user-register-me', 'class' => 'register-form', 'autocomplete' => 'off', 'method' => 'POST']) }}

                                    <input type="hidden" name="action" value= "registration" class="typesubmit" />

                                    <div class="form-wrap-for-rows">
                                        <div class="form-wrap-row form_row">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input form_input">
                                                    <input type="text" name="login" placeholder="Логин" required="required" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-wrap-row form_row">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input form_input">
                                                    <input type="password" name="password" placeholder="Пароль" required="required" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-wrap-row form_row">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input form_input">
                                                    <input type="password"  name="confirm_password" placeholder="Повторите пароль" required="required" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-wrap-row form_row">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input form_input">
                                                    <input type="email" name="email" placeholder="Email" required="required" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-wrap-row form_row">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input form_input">
                                                    <select name="fraction_select" required="required" class="male-select yellow-font">
                                                        <option></option>
                                                        @foreach($races as $key)

                                                            @if($key->race_type == 'race')

                                                                @if( (isset($_GET['fraction'])) && ($_GET['fraction'] == $key->slug) )
                                                                    {{ $selected = 'selected="selected"' }}
                                                                @else
                                                                    {{ $selected = '' }}
                                                                @endif

                                                                <option value="{{ $key->slug }}" {{ $selected }}>{{ $key->title }}</option>

                                                            @endif

                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="reCaptcha-wrap">
                                            <div class="g-recaptcha" data-sitekey="6LfWZyQTAAAAAP3EiGHuaUaTb1t3si4fOBv8E4YK"></div>
                                        </div>

                                        <div class="agree-field">


                                            <input type="checkbox" name="check-agree" id="linka-check" value="false">
                                            <label for="linka-check" class="swicher-maker">
                                                <span class="kvadratic kv-true "></span>
                                                <span>я принимаю условия <br/> <a href="#popup-agree" class="fancybox-form">лицензионного соглашения</a> </span>
                                            </label>

                                        </div>

                                        <div class="form-wrap-row error-text" @if(!empty($errors)) style="display: block;" @endif>

                                            @if(!empty($errors))
                                                @foreach($errors as $error)
                                                    <p>{{ $error }}</p>
                                                @endforeach
                                            @endif

                                        </div>
                                        <div class="form-wrap-row submit">
                                            <div class="form-wrap-value">
                                                <div class="form-wrap-input">
                                                    <button class="form-button" type="submit">
                                                        <span class="form-button-hover"></span>
                                                        <span class="form-button-text">ЗАРЕГЕСТРИРОВАТЬСЯ</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                {{ Form::close() }}
                            </div>

                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden-block">
                <div id="popup-agree">
                    <div class="conteiner-pop">
                        <div class="title">Соглашение</div>
                        <div class="texter">
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean euismod bibendum laoreet. Proin gravida dolor sit amet lacus accumsan et viverra justo commodo. Proin sodales pulvinar tempor. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nam fermentum, nulla luctus pharetra vulputate, felis tellus mollis orci, sed rhoncus sapien nunc eget odio.</p>
                            <ul>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                                <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                            </ul>

                        </div>
                        <div class="confirm">
                            <input type="checkbox" id="confirm-popup">
                            <label for="confirm-popup" class="swicher-maker">
                                <span class="kvadratic"> O </span>
                                <span class="text-confirm">я соглашаюсь с условиями</span>
                            </label>
                        </div>
                        <div class="button-close">
                            <div class="close-form">Закрыть</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop