@extends('admin.layout.default')
@section('content')

    <div class="main-central-wrap">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/magic/add') }}">Добавить</a>
        </div>

        @if($effects -> count())

            <table class="data-table">
                <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Название</th>
                    <th>Ссылка</th>
                    <th>Изображение</th>
                    <th>Раса</th>
                    <th>Действия</th>
                    <th>Цена в золоте</th>
                    <th>Цена в серебре</th>
                    <th>Затраты энергии</th>
                    <th>Создан</th>
                    <th>Изменен</th>
                </tr>
                </thead>
                <tbody>

                @foreach($effects as $effect)

                    <tr>
                        <td><a class="edit" href="{{ URL::asset('admin/magic/edit') }}/{{ $effect->id }}"></a></td>
                        <td>
                            {{ Form::open(['route' => 'admin-magic-drop', 'method' => 'POST']) }}
                            {{ Form::hidden('_method', 'DELETE') }}
                            <input name="effect_id" type="hidden" value="{{ $effect->id }}">
                            <input type="submit" class="drop" value="">
                            {{ Form::close() }}
                        </td>
                        <td>{{ $effect->title }}</td>
                        <td>{{ $effect->slug }}</td>
                        <td>
                            @if($effect->img_url != '')
                                <img src="{{ URL::asset('/img/card_images/'.$effect->img_url) }}" alt="" style="max-width: 100px; max-height: 100px;">
                            @else
                                Изображение отсутсвует
                            @endif
                        </td>
                        <td>
                            <?php
                            $races = unserialize($effect->race);

                            foreach($races as $race){
                                switch($race){
                                    case 'knight':      echo 'Рыцарь империи.<br>'; break;
                                    case 'forest':      echo 'Хозяева леса.<br>'; break;
                                    case 'cursed':      echo 'Проклятые.<br>'; break;
                                    case 'undead':      echo 'Нечисть.<br>'; break;
                                    case 'highlander':  echo 'Горцы.<br>'; break;
                                    case 'monsters':    echo 'Монстры.<br>'; break;
                                }
                            }
                            ?>
                        </td>
                        <td>{!! $effect->description !!}</td>
                        <td>{{ $effect->price_gold }}</td>
                        <td>{{ $effect->price_silver }}</td>
                        <td>{{ $effect->energy_cost }}</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($effect->created_at)) }}</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($effect->updated_at)) }}</td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@stop