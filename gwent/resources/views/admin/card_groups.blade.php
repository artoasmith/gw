@extends('admin.layout.default')
@section('content')

    <div class="main-central-wrap">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/cards/groups/add') }}">Добавить</a>
        </div>

        @if($card_groups -> count())
            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Название</th>
                        <th>Ссылка</th>
                        <th>Карты в группе</th>
                        <th>Создан</th>
                        <th>Изменен</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($card_groups as $group)
                    <tr>
                        <td><a class="edit" href="{{ URL::asset('admin/cards/groups/edit') }}/{{ $group->id }}"></a></td>
                        <td>
                            {{ Form::open(['route' => 'admin-cards-group-drop', 'method' => 'POST']) }}
                            {{ Form::hidden('_method', 'DELETE') }}
                            <input name="group_id" type="hidden" value="{{ $group->id }}">
                            <input type="submit" class="drop" value="">
                            {{ Form::close() }}
                        </td>
                        <td>{{ $group -> title }}</td>
                        <td>{{ $group -> slug }}</td>
                        <td>{!! App\Http\Controllers\AdminViews::cardsViewCardsList($group->has_cards_ids, 'link') !!}</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($group->created_at)) }}</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($group->updated_at)) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

@stop