@extends('admin.layout.default')
@section('content')
<?php
$cards_in_group = unserialize($group[0]['has_cards_ids']);
?>
    <div class="main-central-wrap">
        <input name="group_id" type="hidden" value="{{ $group[0]['id'] }}">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">

        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="group_title" type="text" value="{{ $group[0]['title'] }}"></td>
                </tr>

                <tr>
                    <td style="width: 10%;"><label>Карты:</label></td>
                    <td>
                        <table class="edition" id="currentCardsInGroup">
                            {!! App\Http\Controllers\AdminViews::cardsViewCardsList($group[0]['has_cards_ids'], 'table') !!}
                        </table>

                        <select name="groupCards">

                            @foreach($cards as $card)
                                <option value="{{ $card -> id }}">{{ $card -> title }}</option>
                            @endforeach

                        </select>

                        <input type="button" name="addCardToGroup" value="Добавить карту в группу">
                    </td>
                </tr>
            </table>

        </fieldset>

        <input name="cardGroupEdit" type="button" value="Применить">
    </div>
@stop