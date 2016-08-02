@extends('admin.layout.default')
@section('content')

<div class="main-central-wrap">
    <div class="button-wrap" id="addCard">
        <a class="add-one" href="{{ URL::asset('admin/cards/add') }}">Добавить</a>
    </div>
    
    @if($races -> count())

    <select name="chooseRace">
        @foreach($races as $key => $value)
            <option value="{{ $value->slug }}" @if($race_slug == $value->slug) selected="selected" @endif>{{ $value->title }}</option>
        @endforeach
    </select>
    <table class="data-table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Название</th>
                <th>Изображение</th>
                <th>Сила карты</th>
                <th>Вес карты</th>
                <th>Лидер</th>
                <th>Находится в группах</th>
                <th>Действия</th>
                <th>Цена Общая</th>
                <th>Цена "Только серебро"</th>
                <th>Создан</th>
                <th>Изменен</th>
            </tr>
        </thead>
        <tbody>
        @foreach($cards as $card)

            <tr>
                <td><a class="edit" href="{{ URL::asset('admin/cards/edit') }}/{{ $card->id }}"></a></td>
                <td>
                    {{ Form::open(['route' => 'admin-cards-drop', 'method' => 'POST']) }}
                    {{ Form::hidden('_method', 'DELETE') }}
                    <input name="card_id" type="hidden" value="{{ $card->id }}">
                    <input type="submit" class="drop" value="">
                    {{ Form::close() }}
                </td>
                <td>{{ $card->title }}</td>
                <td>
                    @if($card->img_url != '')
                        <img src="{{ URL::asset('/img/card_images/'.$card->img_url) }}" alt="" style="max-width: 100px; max-height: 100px;">
                    @else
                        Изображение отсутсвует
                    @endif

                </td>
                <td>{{ $card->card_strong }}</td>
                <td>{{ $card->card_value }}</td>
                <td>
                    @if($card->is_leader == 0)
                        Нет
                    @else
                        Да
                    @endif
                </td>
                <td>
                    {!! App\Http\Controllers\AdminViews::cardsViewGetCardGroups($card->id) !!}
                </td>
                <td>
                    {!! App\Http\Controllers\AdminViews::cardsViewCurrentCardActions($card->card_actions) !!}
                </td>
                <td>{{ $card->price_gold}}зол. {{ $card->price_silver }}сер.</td>
                <td>{{ $card->price_only_gold }}</td>
                <td>{{ date('d/m/Y  H:i', strtotime($card->created_at)) }}</td>
                <td>{{ date('d/m/Y  H:i', strtotime($card->updated_at)) }}</td>
            </tr>

        @endforeach
        </tbody>
    </table>
    @endif
</div>
@stop