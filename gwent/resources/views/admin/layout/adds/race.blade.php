@extends('admin.layout.default')
@section('content')

<div class="main-central-wrap">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="race_title" type="text"></td>
            </tr>
            <tr>
                <td><label>Обозначение:</label></td>
                <td><input name="race_slug" type="text"></td>
            </tr>
            <tr>
                <td><label>Тип:</label></td>
                <td><input name="race_type" type="text"></td>
            </tr>
            <tr>
                <td><label>Заглавие описания:</label></td>
                <td><input name="race_text_title" type="text"></td>
            </tr>
            <tr>
                <td><label>Описание:</label></td>
                <td><textarea name="race_text"></textarea></td>
            </tr>
            <tr>
                <td><label>Фон:</label></td>
                <td><input name="raceAddImg" type="file"></td>
            </tr>
        </table>

        <input type="button" name="addRace" value="Добавить">
    </fieldset>
</div>

@stop