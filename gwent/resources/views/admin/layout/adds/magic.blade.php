@extends('admin.layout.default')
@section('content')
    <div class="main-central-wrap">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="magic_title" type="text"></td>
                </tr>
                <tr>
                    <td><label>Описание:</label></td>
                    <td><textarea name="magic_descr"></textarea></td>
                </tr>
                <tr>
                    <td><label>Фон:</label></td>
                    <td><input name="magicAddImg" type="file"></td>
                </tr>
                <tr>
                    <td><label>Расы которые могут использовать:</label></td>
                    <td id="racesToUse">
                        @foreach($races as $race)
                            @if( ($race['slug'] != 'neutrall') && ($race['slug'] != 'special') )
                            <div class="container-wrap">
                                <input type="checkbox" value="{{ $race['slug'] }}">
                                <label>{{ $race['title'] }}</label>
                            </div>
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>

        </fieldset>

        <fieldset>
            <legend>Цены</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Затраты энергии:</label></td>
                    <td><input name="energy_cost" type="number" min="0"></td>
                </tr>
                <tr>
                    <td><label>Цена золото:</label></td>
                    <td><input name="price_gold" type="number" min="0"></td>
                </tr>
                <tr>
                    <td><label>Цена серебро:</label></td>
                    <td><input name="price_silver" type="number" min="0"></td>
                </tr>
            </table>

        </fieldset>
        <input name="magicAdd" type="button" value="Добавить">
    </div>
@stop