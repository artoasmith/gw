@extends('admin.layout.default')
@section('content')
    <div class="main-central-wrap">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <button onclick="location.href='{{ URL::asset('admin/users') }}';">Список пользователей</button>
        <fieldset class="btn-set">
            <legend>Основные действия</legend>
            @if($user->is_banned)
                <button onclick="needban(false,{{$user->id}});" >
                    Разблокировать
                </button>
            @else
                <button onclick="needban(true,{{$user->id}});" >
                    Заблокировать
                </button>
            @endif
            {{ Form::open(['route' => 'admin-users-delete', 'method' => 'POST']) }}
            {{ Form::hidden('_method', 'DELETE') }}
            <input name="id" type="hidden" value="{{ $user->id }}">
            <input type="submit" value="Удалить"/>
            {{ Form::close() }}
        </fieldset>
        <fieldset>
            <legend>Основные данные</legend>
            <table class="edition" style="width: 100%;">
                <tbody>
                    <tr>
                        <td>Номер:</td>
                        <td>{{$user->id}}</td>
                    </tr>
                    <tr>
                        <td style="width: 10%;"><label>Логин:</label></td>
                        <td>{{$user->login}}</td>
                    </tr>
                    <tr>
                        <td>Email:</td>
                        <td>{{$user->email}}</td>
                    </tr>
                    <tr>
                        <td>Изображение:</td>
                        <td>
                            {!!
                                $user->img_url ?
                                    sprintf('<img src="%s" alt="" style="max-width: 100px; max-height: 100px;">',URL::asset('/img/user_images/'.$user->img_url))
                                    :
                                    'Изображение отсутсвует'
                            !!}
                        </td>
                    </tr>
                    <tr>
                        <td>Создан:</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($user->created_at)) }}</td>
                    </tr>
                    <tr>
                        <td>Активность:</td>
                        <td>{{ date('d/m/Y  H:i', strtotime($user->updated_at)) }}</td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    </div>
@stop()