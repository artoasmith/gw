@extends('admin.layout.default')
@section('content')
<?php
$current_errors = $errors->all();
?>
<div class="main-wrap clearfix">
    {{ Form::open(['route' => 'admin-manager-edit', 'method' => 'POST']) }}
    <input name="_method" type="hidden" value="PUT">
    <input name="adm_id" type="hidden" value="{{ $user[0]['id'] }}">
    <input name="adm_login" type="hidden" value="{{ $user[0]['login'] }}">
    <fieldset>
        <legend>Основные данные {{ $user[0]['login'] }}</legend>
        <table class="edition">
            <tr>
                <td><label>Пароль:</label></td>
                <td><input name="adm_password" type="password"></td>
                <td>
                    <?php
                    if(isset($current_errors['pass'])){
                        echo '<p class="errors">'.$current_errors['pass'].'</p>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><label>Подтвердите пароль:</label></td>
                <td><input name="adm_confirm_password" type="password"></td>
                <td>
                    <?php
                    if(isset($current_errors['conf_pass'])){
                        echo '<p class="errors">'.$current_errors['conf_pass'].'</p>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><label>e-mail:</label></td>
                <td><input name="adm_email" type="text" value="{{ $user[0]['email'] }}"></td>
            </tr>
            <tr>
                <td><label>Телефон:</label></td>
                <td><input name="adm_phone" type="text" value="{{ $user[0]['phone'] }}"></td>
            </tr>
            <tr>
                <td><label>Имя:</label></td>
                <td><input name="adm_name" type="text"value="{{ $user[0]['name'] }}"></td>
            </tr>
            <tr>
                <td></td>
                <td>{{ Form::submit('Пименить') }}</td>
            </tr>
        </table>
    </fieldset>
    {{ Form::close() }}
</div>
@stop