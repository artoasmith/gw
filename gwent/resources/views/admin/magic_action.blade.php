@extends('admin.layout.default')
@section('content')

    <div class="main-central-wrap">
        <div class="button-wrap">
            <a class="add-one" href="{{ URL::asset('admin/magic/actions/add') }}">Добавить</a>
        </div>

    @if($magic_actions -> count())
        <table class="data-table">
            <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Название</th>
                <th>Описание</th>
                <th>HTML</th>
                <th>Создан</th>
                <th>Изменен</th>
            </tr>
            </thead>
            <tbody>
            @foreach($magic_actions as $action)
                <tr>
                    <td><a class="edit" href="{{ URL::asset('admin/magic/actions/edit') }}/{{ $action->id }}"></a></td>
                    <td>
                        {{ Form::open(['route' => 'admin-magic-actions-drop', 'method' => 'POST']) }}
                        {{ Form::hidden('_method', 'DELETE') }}
                        <input name="adm_id" type="hidden" value="{{ $action->id }}">
                        <input type="submit" class="drop" value="">
                        {{ Form::close() }}
                    </td>
                    <td>{{ $action -> title }}</td>
                    <td>{{ Str::limit($action -> description, 50, '...') }}</td>
                    <td class="tal">
                        <?php
                        $html_options = unserialize($action -> html_options);
                        $n = count($html_options);
                        ?>
                        @for($i=0; $i<$n; $i++)
                            <p>{!! $html_options[$i][0] !!}</p>
                            {!! $html_options[$i][1] !!}
                            <hr>
                        @endfor
                    </td>
                    <td>{{ date('d/m/Y  H:i', strtotime($action->created_at)) }}</td>
                    <td>{{ date('d/m/Y  H:i', strtotime($action->updated_at)) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
    </div>
@stop