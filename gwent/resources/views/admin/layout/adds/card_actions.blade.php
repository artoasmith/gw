@extends('admin.layout.default')
@section('content')

<div class="main-central-wrap">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="action_title" type="text"></td>
            </tr>
            <tr>
                <td><label>Описание:</label></td>
                <td><textarea name="action_descr"></textarea></td>
            </tr>
        </table>

    </fieldset>

    <fieldset>
        <legend>Характеристики:</legend>
        <table class="edition" style="width: 100%;" id="card_action_characteristic_table">
            <tr>
                <td style="width: 10%; vertical-align: top;"><input name="action_characteristic_label" type="text"></td>
                <td><textarea name="action_characteristic_html"></textarea></td>
            </tr>
        </table>
        <input name="action_add_characteristic" type="button" value="Добавить Характеристику">
    </fieldset>

    <input name="cardActionAdd" type="button" value="Добавить">
</div>
@stop