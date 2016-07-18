@extends('admin.layout.default')
@section('content')

    <div class="main-central-wrap">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <input name="race_id" type="hidden" value="{{ $race[0]['id'] }}">
        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="race_title" type="text" value="{{ $race[0]['title'] }}"></td>
                </tr>
                <tr>
                    <td><label>Обозначение:</label></td>
                    <td><input name="race_slug" type="text" value="{{ $race[0]['slug'] }}"></td>
                </tr>
                <tr>
                    <td><label>Тип:</label></td>
                    <td><input name="race_type" type="text" value="{{ $race[0]['race_type'] }}"></td>
                </tr>
                <tr>
                    <td><label>Заглавие описания:</label></td>
                    <td><input name="race_text_title" type="text" value="{{ $race[0]['description_title'] }}"></td>
                </tr>
                <tr>
                    <td><label>Описание:</label></td>
                    <td><textarea name="race_text">{{ $race[0]['description'] }}</textarea></td>
                </tr>
                <tr>
                    <td><label>Фон:</label></td>
                    <td>
                        <input name="raceAddImg" type="file">
                        @if($race[0]['img_url'] !='')
                            <img id="raceImage" src="{{ URL::asset('/img/card_images/'.$race[0]['img_url']) }}" alt="{{ $race[0]['img_url'] }}" style="max-width: 100px; max-height: 100px;">
                        @endif
                    </td>
                </tr>
            </table>

            <input type="button" name="editRace" value="Применить">
        </fieldset>
    </div>

@stop