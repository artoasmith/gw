@extends('admin.layout.default')
@section('content')

    <?php
    $charac = unserialize($magic_actions[0]['html_options']);
    ?>
    <div class="main-central-wrap">
        <input name="action_id" type="hidden" value="{{ $magic_actions[0]['id'] }}">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="action_title" type="text" value="{{ $magic_actions[0]['title'] }}"</td>
                </tr>
                <tr>
                    <td><label>Описание:</label></td>
                    <td><textarea name="action_descr">{{ $magic_actions[0]['description'] }}</textarea></td>
                </tr>
            </table>

        </fieldset>

        <fieldset>
            <legend>Характеристики:</legend>
            <table class="edition" style="width: 100%;" id="card_action_characteristic_table">
                @if(count($charac) > 0)
                    @for ($i=0; $i<count($charac); $i++)
                        <tr>
                            <td style="width: 10%; vertical-align: top;">
                                <input name="action_characteristic_label" type="text" value="{{ $charac[$i][0] }}">
                            </td>
                            <td><textarea name="action_characteristic_html">{{ $charac[$i][1] }}</textarea></td>
                        </tr>
                    @endfor
                @else
                    <tr>
                        <td style="width: 10%; vertical-align: top;">
                            <input name="action_characteristic_label" type="text">
                        </td>
                        <td><textarea name="action_characteristic_html"></textarea></td>
                    </tr>
                @endif
            </table>
            <input name="action_add_characteristic" type="button" value="Добавить Характеристику">
        </fieldset>

        <input name="magicActionEdit" type="button" value="Применить">
    </div>
@stop