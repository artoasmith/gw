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
                        <th>Золото</th>
                        <th>Серебро</th>
                        <th>Энергия</th>
                        <th>Создан</th>
                        <th>Активность</th>
                        <th>Бан</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <?php
                        $user_data = \DB::table('tbl_user_data')->select('user_id', 'user_gold', 'user_silver', 'user_energy')->where('user_id', '=', $user->id)->get();
                        ?>
                        <tr>
                            <td><a class="edit" href="{{ URL::asset(sprintf('admin/users/view/%d',$user->id)) }}"></a></td>
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
                            <td>{{ $user_data[0]->user_gold }}</td>
                            <td>{{ $user_data[0]->user_silver }}</td>
                            <td>{{ $user_data[0]->user_energy }}</td>
                            <td>{{ date('d/m/Y  H:i', strtotime($user->created_at)) }}</td>
                            <td>{{ date('d/m/Y  H:i', strtotime($user->updated_at)) }}</td>
                            @if($user->is_banned)
                                <td title="Разблокировать" onclick="needban(false,{{$user->id}});" style="background-color: #f00;">
                                    Помиловать
                                </td>
                            @else
                                <td  title="Заблокировать" onclick="needban(true,{{$user->id}});">
                                    Забанить
                                </td>
                            @endif
                            <td>
                                @if($user->user_role)
                                    Администратор
                                @else
                                    Пользователь
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                {{ $users -> links() }}
            </div>
        @endif
    </div>
@stop