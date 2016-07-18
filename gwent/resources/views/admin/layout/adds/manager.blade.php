@extends('admin.layout.default')
@section('content')

<?php
$current_errors = $errors -> all();
?>

<div class="main-wrap clearfix">
    {{ Form::open(['route' => 'admin-manager-add', 'method' => 'POST']) }}
    <fieldset>
        <legend>Основные данные</legend>
        <table class="edition">
            <tr>
                <td><label>Логин:</label></td>
                <td><input name="adm_login" type="text"></td>
                <td>
                    <?php
                    if(isset($current_errors['login'])){
                        echo '<p class="errors">'.$current_errors['login'].'</p>';
                    }
                    ?>
                </td>
            </tr>
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
                <td><input name="adm_email" type="text"></td>
            </tr>
            <tr>
                <td><label>Телефон:</label></td>
                <td><input name="adm_phone" type="text"></td>
            </tr>
            <tr>
                <td><label>Имя:</label></td>
                <td><input name="adm_name" type="text"></td>
            </tr>
            <tr>
                <td></td>
                <td>{{ Form::submit('Добавить') }}</td>
            </tr>
        </table>
    </fieldset>
    
    {{ Form::close() }}
</div>
@stop