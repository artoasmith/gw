@extends('layouts.default')
@section('content')

<?php
$user = Auth::user();
$errors = $errors->all();
?>
@if($user)

    @include('layouts.top')

    <div class="main not-main">
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

            <div class="content-wrap settings-page">
                <div class="form-wrap preloading-class">

                    <div class="preloader" style="display: none;">
                        <img src="{{ URL::asset('images/359.gif') }}" alt="">
                    </div>

                    <div class="form-wrap-main">

                        {{ Form::open(['route' => 'user-settings-change', 'method' => 'POST', 'class' => 'settings_form', 'autocomplete' => 'off']) }}
                        {{ Form::hidden('_method', 'PUT') }}

                        <div class="form-title">ЛИЧНЫЕ ДАННЫЕ</div>
                        <div class="form-description">
                            <div class="form-description-settings-img">

                                <div class="faceman">
                                    <div class="form-description-settings-img-wrap">
                                        @if($user['img_url'] != '')
                                            <img id="avatarImg" src="{{ URL::asset('img/user_images/'.$user['img_url']) }}" alt="">
                                        @endif

                                    </div>
                                </div>

                                <div class="form-description-settings-inp">
                                    <div class="form-description-settings-inp-wrap">
                                        <input type="file" id="" name="image_user" accept="image/jpeg, image/jpg, image/png, image/gif, image/*">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-wrap-item">
                                <input type="hidden" name="action" value= "user_settings" class="typesubmit" />
                                <div class="form-title">{{ $user['login'] }}</div>
                                <div class="form-wrap-for-rows">
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Email</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input value="{{ $user['email'] }}" type="email" name="settings_email"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Текущий пароль</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input value="" type="password" name="current_password"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Новый пароль</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input value="" type="password" name="settings_pass"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Подтверждение</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input value="" type="text" name="settings_pass_confirm"/>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Имя, фамилия</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input type="text" name="settings_name" value="{{ $user['name'] }}" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Дата рождения</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <input type="date" name="settings_birth_date" value="{{ $user['birth_date'] }}" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-wrap-row form_row">
                                        <div class="form-wrap-key">Пол</div>
                                        <div class="form-wrap-value">
                                            <div class="form-wrap-input form_input">
                                                <select name="settings_gender" style="width: 100%;">
                                                <?php
                                                $gender_array = ['male' => 'Мужской', 'female' => 'Женский', 'other' => 'Другой'];

                                                foreach($gender_array as $en => $ru){
                                                    if($user['user_gender'] == $en){
                                                        $selected = 'selected="selected"';
                                                    }else{
                                                        $selected = '';
                                                    }

                                                    echo '<option value="'.$en.'" '.$selected.'>'.$ru.'</option>';
                                                }
                                                ?>

                                                </select>
                                            </div>
                                        </div>
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
                                                <button class="form-button" type="submit" name="settingsChange">
                                                    <span class="form-button-hover"></span>
                                                    <span class="form-button-text">СОХРАНИТЬ</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        </div>

                        {{ Form::close() }}

                    </div>
                </div>
            </div>

        </div>
    </div>
@endif

@stop