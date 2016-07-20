@extends('admin.layout.default')
@section('content')
    <div class="main-central-wrap">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        @if($errors->count())
            @foreach($errors as $error)
                <span style="color: red;">{{ $error }}</span>
            @endforeach
        @endif
        @if(!(count($users)))
            список пуст
        @else
            <div class="table-wrap">

                <table class="data-table">
                    <thead>
                    <tr>
                        <th></th>
                        <th></th>
                        <th>Изображение</th>
                        <th>Логин</th>
                        <th>Email</th>
                        <?php /*<th>Сила карты</th>
                        <th>Вес карты</th>
                        <th>Лидер</th>
                        <th>Находится в группах</th>
                        <th>Действия</th>
                        <th>Цена Общая</th>
                        <th>Цена "Только Золото"</th>*/?>
                        <th>Создан</th>
                        <th>Активность</th>
                        <th>Бан</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><a class="list-icon" href="{{ URL::asset(sprintf('admin/users/view/%d',$user->id)) }}"></a></td>
                            <td>
                                {{ Form::open(['route' => 'admin-users-delete', 'method' => 'POST']) }}
                                {{ Form::hidden('_method', 'DELETE') }}
                                <input name="id" type="hidden" value="{{ $user->id }}">
                                <input type="submit" class="drop" value="">
                                {{ Form::close() }}
                            </td>
                            <td>
                                {!!
                                    $user->img_url ?
                                        sprintf('<img src="%s" alt="" style="max-width: 100px; max-height: 100px;">',URL::asset('/img/user_images/'.$user->img_url))
                                        :
                                        'Изображение отсутсвует'
                                !!}
                            </td>
                            <td>{{ $user->login }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ date('d/m/Y  H:i', strtotime($user->created_at)) }}</td>
                            <td>{{ date('d/m/Y  H:i', strtotime($user->updated_at)) }}</td>
                            @if($user->is_banned)
                                <td title="Разблокировать" onclick="needban(false,{{$user->id}});" style="background-color: #f00;">
                                    Помиловать
                                </td>
                            @else
                                <td  title="Заблокировать" onclick="needban(true,{{$user->id}});">
                                    {!! sprintf('<img src="%s" alt="" style="max-width: 25px; max-height: 25px;">',URL::asset('/img/ban.png')) !!}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $users -> links() }}
            </div>
        @endif
    </div>
@stop