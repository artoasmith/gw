@extends('admin.layout.default')
@section('content')
<?php
$current_errors = $errors->all();
$user_data = \DB::table('tbl_user_data')->select('user_id','user_gold', 'user_silver', 'user_energy')->where('user_id', '=', $user->id)->get();
?>
<div class="main-wrap clearfix">
    {{ Form::open(['route' => 'admin-manager-edit', 'method' => 'POST']) }}
    <input name="_method" type="hidden" value="PUT">
    <input name="user_id" type="hidden" value="{{ $user->id }}">
    <input name="user_login" type="hidden" value="{{ $user->login }}">
    <fieldset>
        <legend>Основные данные {{ $user->login }}</legend>
        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>e-mail:</label></td>
                <td><input name="user_email" type="text" value="{{ $user->email }}"></td>
            </tr>
            <tr>
                <td><label>Никнейм:</label></td>
                <td><input name="user_nickname" type="text" value="{{ $user->nickname }}"></td>
            </tr>
            <tr>
                <td><label>Имя:</label></td>
                <td><input name="user_name" type="text" value="{{ $user->name }}"></td>
            </tr>
            <tr>
                <td><label>Пол:</label></td>
                <td><input name="user_gender" type="text" value="{{ $user->user_gender }}"></td>
            </tr>
            <tr>
                <td><label>Дата рожденья:</label></td>
                <td><input name="user_birthday" type="text" value="{{ $user->birth_date  }}"></td>
            </tr>
            <tr>
                <td><label>Адресс:</label></td>
                <td><textarea name="user_address">{{ $user->address }}</textarea></td>
            </tr>

            <tr>
                <td><label>Золото:</label></td>
                <td><input name="user_gold" type="text" value="{{ $user_data[0]->user_gold }}"></td>
            </tr>
            <tr>
                <td><label>Серебро:</label></td>
                <td><input name="user_silver" type="text" value="{{ $user_data[0]->user_silver }}"></td>
            </tr>
            <tr>
                <td><label>Энергия:</label></td>
                <td><input name="user_energy" type="text" value="{{ $user_data[0]->user_energy }}"></td>
            </tr>
            <tr>
                <td><label>Адимнистратор:</label></td>
                <td>
                <?php
                if($user->user_role == 1){
                    $checked = 'checked="checked"';
                }else{
                    $checked = '';
                }
                ?>
                    <input name="user_role" type="checkbox" {!! $checked !!}>
                </td>
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