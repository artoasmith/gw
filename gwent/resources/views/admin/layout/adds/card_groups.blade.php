@extends('admin.layout.default')
@section('content')

<div class="main-central-wrap">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">

    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="group_title" type="text"></td>
            </tr>

            <tr>
                <td style="width: 10%;"><label>Карты:</label></td>
                <td>
                    <table class="edition" id="currentCardsInGroup">
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

    <input name="cardGroupAdd" type="button" value="Добавить">
</div>
@stop