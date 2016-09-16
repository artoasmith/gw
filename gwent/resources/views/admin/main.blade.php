@extends('admin.layout.default')
@section('content')

<ul class="bookmark_menu clearfix">
    <li class="active" data-link="cardRaces">Рассы</li>
    <li data-link="leagueOptions">Настройки лиг</li>
    <li data-link="baseCards">Базовые карты</li>
    <li data-link="baseUserFields">Базовые поля пользователей</li>
    <li data-link="exchangeOptions">Соотношение обменов</li>
    <li data-link="deckOptions">Настройка колоды</li>
</ul>

<!-- Расы -->
<div class="main-central-wrap" id="cardRaces">
    <div class="button-wrap">
        <a class="add-one" href="{{ URL::asset('admin/race/add') }}">Добавить</a>
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Название</th>
                <th>Обозначение</th>
                <th>Тип</th>
                <th>Изображение</th>
                <th>Дата создания</th>
                <th>Дата изменения</th>
            </tr>
        </thead>

        <tbody>
        @foreach($races as $race)
            <tr>
                <td><a class="edit" href="{{ URL::asset('admin/race/edit') }}/{{ $race->id }}"></a></td>
                <td>
                    {{ Form::open(['route' => 'admin-races-drop', 'method' => 'POST']) }}
                    {{ Form::hidden('_method', 'DELETE') }}
                    <input name="race_id" type="hidden" value="{{ $race->id }}">
                    <input type="submit" class="drop" value="">
                    {{ Form::close() }}
                </td>
                <td>{{ $race -> title }}</td>
                <td>{{ $race -> slug }}</td>
                <td>
                <?php
                    switch($race -> race_type){
                        case 'race':    echo 'Рассовые карты'; break;
                        case 'special': echo 'Специальные карты'; break;
                        case 'neutrall':echo 'Нейтральные карты'; break;
                        default: echo $race -> race_type;
                    }
                ?>
                </td>
                <td>
                    @if($race -> img_url != '')
                        <img src="{{ URL::asset('/img/card_images/'.$race->img_url) }}" alt="" style="max-width: 100px; max-height: 100px;">
                    @else
                        Изображение отсутсвует
                    @endif
                </td>
                <td>{{ date('d/m/Y  H:i', strtotime($race->created_at)) }}</td>
                <td>{{ date('d/m/Y  H:i', strtotime($race->updated_at)) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<!-- END OF Расы -->



<!-- Настройки лиг -->
<div class="main-central-wrap" id="leagueOptions" style="display:none;">
    <table class="edition">
        <thead>
            <tr>
                <th></th>
                <th>Лига</th>
                <th>От</th>
                <th>До</th>
            </tr>
        </thead>
        <tbody>
        @foreach($leagues as $league)
            <tr>
                <td><a href="#" class="drop"></a></td>
                <td><input name="league_title" type="text" value="{{ $league->title }}"></td>
                <td><input name="league_min" type="number" value="{{ $league->min_lvl }}"></td>
                <td><input name="league_max" type="number" value="{{ $league->max_lvl }}"></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="container-wrap">
        <input name="leagueAddRow" type="button" value="Добавить строку">
    </div>

    <div class="container-wrap">
        <input name="leagueApply" type="button" value="Применить">
    </div>
</div>
<!-- END OF Настройки лиг-->


<!-- Базовые карты -->
<div class="main-central-wrap" id="baseCards" style="display:none;">
    @foreach($races as $race)
        @if($race -> race_type == 'race')
            <!-- {{ $race-> title }}-->
            <fieldset>
                <legend>Базовые карты колоды "{{ $race-> title }}"</legend>

                <table class="edition">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Карта</th>
                            <th>Количество</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    $race_deck = unserialize($race->base_card_deck);
                    ?>

                    @foreach($race_deck as $deck)
                    <tr>
                        <td><a href="#" class="drop"></a></td>
                        <td>{!! App\Http\Controllers\AdminViews::getAllCardsSelectorView( $deck->id ) !!}</td>
                        <td><input type="number" name="currentQuantity" value="{{ $deck->q }}"></td>
                    </tr>
                    @endforeach

                    </tbody>
                </table>

                <div class="container-wrap">
                    <input name="baseCardsAddRow" type="button" value="Добавить Строку">
                </div>
                <div class="container-wrap">
                    <input name="baseCardsApply" type="button" value="Применить" id="{{ $race->slug }}">
                </div>
             </fieldset>
        @endif
    @endforeach
</div>
<!-- END OF Базовые карты -->

<!-- Базовые поля пользователей -->
<div class="main-central-wrap" id="baseUserFields" style="display:none;">
    {{ Form::open(['route' => 'admin-baseUserFields', 'method' => 'POST']) }}
    {{ Form::hidden('_method', "PUT") }}
    <table class="edition" style="width: 100%">
        <tbody>
            <?php
            $base_user_fields = App\EtcDataModel::where('label_data', '=', 'base_user_fields')->get();
            ?>
            @foreach($base_user_fields as $field)
                <tr>
                    <td style="max-width: 20%; width:20%;">{{ Form::label($field->meta_key, $field->meta_key_title.':') }}</td>
                    <td>{{ Form::input('text', $field->meta_key, $field->meta_value) }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>
    {{ Form::submit('Применить') }}
    {{ Form::close() }}
</div>
<!--END OF Базовые поля пользователей -->

<!-- Соотношение обменов -->
<div class="main-central-wrap" id="exchangeOptions" style="display:none;">
    {{ Form::open(['route' => 'admin-exchange-change', 'method' => 'POST']) }}
    {{ Form::hidden('_method', "PUT") }}

    <table class="edition" style="width: 100%">
        <tbody>
        <?php
        $exchange = App\EtcDataModel::where('label_data', '=', 'exchange_options')->get();
        ?>
        @foreach($exchange as $field)
            <tr>
                <td style="max-width: 15%; width:15%;">{!! $field->meta_key_title.':' !!}</td>
                <td>{{ Form::input('text', $field->meta_key, $field->meta_value) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ Form::submit('Применить') }}
    {{ Form::close() }}
</div>
<!--END OF Соотношение обменов -->

<!-- Настройка колоды -->
<div class="main-central-wrap" id="deckOptions" style="display:none;">
    {{ Form::open(['route' => 'admin-deck-options', 'method' => 'POST']) }}
    {{ Form::hidden('_method', "PUT") }}

    <table class="edition" style="width: 100%">
        <tbody>
        <?php
        $deck_options = App\EtcDataModel::where('label_data', '=', 'deck_options')->get();
        ?>
        @foreach($deck_options as $field)
            <tr>
                <td style="max-width: 20%; width: 20%;">{{ Form::label($field->meta_key, $field->meta_key_title.':') }}</td>
                <td>{{ Form::input('text', $field->meta_key, $field->meta_value) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ Form::submit('Применить') }}
    {{ Form::close() }}
</div>
<!--END OF Настройка колоды -->
@stop