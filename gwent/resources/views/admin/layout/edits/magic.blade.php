@extends('admin.layout.default')
@section('content')
    <script src="{{ URL::asset('js/within_magic.js') }}"></script>

    <div class="main-central-wrap">
        <input name="_token" type="hidden" value="{{ csrf_token() }}">
        <input name="effect_id" type="hidden" value="{{ $effect[0]['id'] }}">
        <fieldset>
            <legend>Основные данные</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Название:</label></td>
                    <td><input name="magic_title" type="text" value="{{ $effect[0]['title'] }}"></td>
                </tr>
                <tr>
                    <td><label>Описание:</label></td>
                    <td><textarea name="magic_descr">{{ $effect[0]['description'] }}</textarea></td>
                </tr>
                <tr>
                    <td><label>Фон:</label></td>
                    <td>
                        <input name="magicAddImg" type="file">
                        @if(($effect[0]['img_url'] != '') && ($effect[0]['img_url'] != 'undefined'))
                        <img id="oldImgUrl" src="/img/card_images/{{ $effect[0]['img_url'] }}" alt="{{ $effect[0]['img_url'] }}">
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><label>Расы которые могут использовать:</label></td>
                    <td id="racesToUse">
                        <?php
                        $current_races = unserialize($effect[0]['race']);
                        foreach($races as $race){
                            if( ($race['slug'] != 'neutrall') && ($race['slug'] != 'special') ){
                                if( in_array($race['slug'], $current_races, true) ){
                                    $checked = 'checked="checked"';
                                }else{
                                    $checked = '';
                                }
                                ?>

                            <div class="container-wrap">
                                <input type="checkbox" value="{{ $race['slug'] }}" {!! $checked !!}>
                                <label>{{ $race['title'] }}</label>
                            </div>

                            <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </fieldset>

        <fieldset>
            <legend>Действия</legend>

            <table class="edition" id="magicCurrentActions">
                <tbody>
                {!! App\Http\Controllers\AdminViews::cardsViewGetMagicActions($effect[0]['effect_actions']) !!}
                </tbody>
            </table>

            <table class="actions" id="tableMagicActionList">
                <thead>
                <tr>
                    <td><strong>Выбрать действие:</strong></td>
                    <td>
                        <select name="magic_actions_select">
                            @foreach($magic_actions as $action)
                                <option value="{{ $action['id'] }}" data-title="{{ $action['slug'] }}">{{ $action['title'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                    </td>
                </tr>
                </thead>

                <tbody>
                </tbody>

            </table>

            <div style="padding: 5px 20px 5px 2%;">
                <input type="button" name="addMoreMagicActions" value="Добавить действие">
            </div>
        </fieldset>

        <fieldset>
            <legend>Цены</legend>

            <table class="edition" style="width: 100%;">
                <tr>
                    <td style="width: 10%;"><label>Затраты энергии:</label></td>
                    <td><input name="energy_cost" type="number" min="0" value="{{ $effect[0]['energy_cost'] }}"></td>
                </tr>
                <tr>
                    <td><label>Цена золото:</label></td>
                    <td><input name="price_gold" type="number" min="0" value="{{ $effect[0]['price_gold'] }}"></td>
                </tr>
                <tr>
                    <td><label>Цена серебро:</label></td>
                    <td><input name="price_silver" type="number" min="0" value="{{ $effect[0]['price_silver'] }}"></td>
                </tr>
            </table>

        </fieldset>
        <input name="magicEdit" type="button" value="Применить">
    </div>
@stop