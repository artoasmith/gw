@extends('admin.layout.default')
@section('content')

<script src="{{ URL::asset('js/within_cards.js') }}"></script>

<div class="main-central-wrap">

    <input name="card_id" type="hidden" value="{{ $card[0]['id'] }}">
    <input name="_token" type="hidden" value="{{ csrf_token() }}">
    <fieldset>
        <legend>Основные данные</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Название:</label></td>
                <td><input name="card_title" type="text" value="{{ $card[0]['title'] }}"></td>
            </tr>
            <tr>
                <td><label>Короткое описание:</label></td>
                <td><textarea name="card_short_descr">{{ $card[0]['short_description'] }}</textarea></td>
            </tr>
            <tr>
                <td><label>Полное описание:</label></td>
                <td><textarea name="card_full_descr">{{ $card[0]['full_description'] }}</textarea></td>
            </tr>
            <tr>
                <td><label>Фон:</label></td>
                <td>
                    <input name="cardAddImg" type="file">
                    @if($card[0]['img_url'] !='')
                        <img id="cardImage" src="{{ URL::asset('/img/card_images/'.$card[0]['img_url']) }}" alt="{{ $card[0]['img_url'] }}" style="max-width: 100px; max-height: 100px;">
                    @endif
                </td>
            </tr>
        </table>

    </fieldset>



    <fieldset>
        <legend>Отнести к группе:</legend>
        <table class="edition" id="cardCurrentGroups">
        <tbody>
        {!! App\Http\Controllers\AdminViews::cardsViewGetCardGroups($card[0]['id'], $type='table') !!}
        </tbody>
        </table>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 10%;"><label>Отнести к группе:</label></td>
                <td>
                    <select name="addCardToGroup">

                    </select>
                </td>
            </tr>
        </table>

        <div style="padding: 5px 20px 5px 2%;">
            <input type="button" name="addGroup" value="Добавить группу">
        </div>
    </fieldset>



    <fieldset>
        <legend>Действия</legend>
        <table class="edition" id="cardCurrentActions">
        <tbody>
        {!! App\Http\Controllers\AdminViews::cardsViewGetCardActions($card[0]['card_actions']) !!}
        </tbody>
        </table>

        <table class="actions" id="tableActionList">
        <thead>
            <tr>
                <td><strong>Выбрать действие:</strong></td>
                <td>
                    <select name="card_actions_select">
                        @foreach($card_actions as $action)
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
            <input type="button" name="addMoreCardActions" value="Добавить действие">
        </div>
    </fieldset>

    <fieldset>
        <legend>Описание</legend>

        <?php
        switch($card[0]['card_type']){
            case 'neutrall':
                $options = ['selected="selected"', '', ''];
                break;
            case 'race':
                $options = ['', 'selected="selected"', ''];
                break;
            case 'special':
                $options = ['', '', 'selected="selected"'];
                break;
        }
        ?>

        <table class="actions" style="width: 100%;">
            <tr>
                <td style="width: 15%;"><label>Тип карты:</label></td>
                <td>
                    <select name="cardType">
                        <option value="neutrall" {!! $options[0] !!}>Нейтральная</option>
                        <option value="race" {!! $options[1] !!}>Расовая</option>
                        <option value="special" {!! $options[2] !!}>Специальная</option>
                    </select>
                </td>
            </tr>

            <?php
            $forbidden_races = unserialize($card[0]['forbidden_races']);
            $options = ['knight' => '', 'forest' => '', 'cursed' => '', 'undead' => '', 'highlander' => '', 'monsters' => ''];

            foreach($forbidden_races as $race){
                $options[$race] = 'checked="checked"';
            }
            ?>
            <tr id="cardCanNotBeSavedByRace">
                <td><label>Карта не может быть собрана в колоде расы:</label></td>
                <td>
                    <div class="container-wrap">
                        <input type="checkbox" value="knight" <?= $options['knight']; ?>>
                        <label>Рыцари империи</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="forest" <?= $options['forest']; ?>>
                        <label>Хозяева леса</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="cursed" <?= $options['cursed']; ?>>
                        <label>Проклятые</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="undead" <?= $options['undead']; ?>>
                        <label>Нечисть</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="highlander" <?= $options['highlander']; ?>>
                        <label>Горцы</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="monsters" <?= $options['monsters']; ?>>
                        <label>Монстры</label>
                    </div>
                </td>
            </tr>

            <?php
            $options = ['knight' => '', 'forest' => '', 'cursed' => '', 'undead' => '', 'highlander' => '', 'monsters' => ''];
            $options[$card[0]['card_race']] = 'selected="selected"';
            ?>
            <tr id="cardInRace" style="display: none;">
                <td><label>Отностися к расе:</label></td>
                <td>
                    <select name="cardRace">
                        <option value="knight" <?= $options['knight']; ?>>Рыцари империи</option>
                        <option value="forest" <?= $options['forest']; ?>>Хозяева леса</option>
                        <option value="cursed" <?= $options['cursed']; ?>>Проклятые</option>
                        <option value="undead" <?= $options['undead']; ?>>Нечисть</option>
                        <option value="highlander" <?= $options['highlander']; ?>>Горцы</option>
                        <option value="monsters" <?= $options['monsters']; ?>>Монстры</option>
                    </select>
                </td>
            </tr>

            <?php
            $options = ['','',''];
            $allowed_rows = unserialize($card[0]['allowed_rows']);
            foreach($allowed_rows as $row){
                switch($row){
                    case '0': $options[0] = 'checked="checked"'; break;
                    case '1': $options[1] = 'checked="checked"'; break;
                    case '2': $options[2] = 'checked="checked"'; break;
                }
            }

            ?>
            <tr>
                <td><label>Дальность карты:</label></td>
                <td>
                    <div class="container-wrap">
                        <input type="checkbox" value="0" name="C_ActionRow" <?= $options[0];?>>
                        <label>Ближний</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="1" name="C_ActionRow" <?= $options[1];?>>
                        <label>Дальний</label>
                    </div>
                    <div class="container-wrap">
                        <input type="checkbox" value="2" name="C_ActionRow" <?= $options[2];?>>
                        <label>Сверхдальний</label>
                    </div>
                </td>
            </tr>

            <tr>
                <td><label>Сила карты:</label></td>
                <td><input name="cardStrongthValue" type="number" min="0" value="{{ $card[0]['card_strong'] }}"></td>
            </tr>

            <tr>
                <td><label>Вес карты:</label></td>
                <td><input name="cardWeightValue" type="number" min="0" value="{{ $card[0]['card_value'] }}"></td>
            </tr>

            <?php
            if(0 != $card[0]['is_leader']){
                $checked = 'checked="checked"';
            }else{
                $checked = '';
            }
            ?>
            <tr>
                <td><label>Карта лидер:</label></td>
                <td><input name="cardIsLeader" type="checkbox" <?= $checked ?>><label>Сделать текущую карту картой-лидером</label></td>
            </tr>

            <tr>
                <td><label>Максимальное колличество в колоде:</label></td>
                <td><input name="cardMaxValueInDeck" type="number" min="1" value="{{ $card[0]['max_quant_in_deck'] }}"></td>
            </tr>
        </table>
    </fieldset>


    <fieldset>
        <legend>Цены</legend>

        <table class="edition" style="width: 100%;">
            <tr>
                <td style="width: 15%; min-width: 15%"><label>Цена золото:</label></td>
                <td><input name="cardPriceGold" type="number" min="0" value="{{ $card[0]['price_gold'] }}"></td>
            </tr>
            <tr>
                <td><label>Цена серебро:</label></td>
                <td><input name="cardPriceSilver" type="number" min="0" value="{{ $card[0]['price_silver'] }}"></td>
            </tr>
            <tr><td style="padding: 15px;"></td><td></td></tr>
            <tr>
                <td><label>Цена <strong>Только золото</strong>:</label></td>
                <td><input name="cardPriceGoldOnly" type="number" min="0" value="{{ $card[0]['price_only_gold'] }}"></td>
            </tr>
        </table>
    </fieldset>

    <input name="cardEdit" type="button" value="Применить">
</div>
@stop