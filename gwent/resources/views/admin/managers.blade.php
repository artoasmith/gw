@extends('admin.layout.default')
@section('content')

<div class="main-wrap clearfix">
    
    <div class="button-wrap">
        <a class="add-one" href="{{ URL::asset('admin/admins/add') }}">Добавить</a>
    </div>
    
    <div class="main-central-wrap">
        @if($users -> count())
        <table class="data-table">
            <thead>
                <tr>
                    <th></th>
                    <th></th>
                    <th>Логин</th>
                    <th>e-mail</th>
                    <th>Телефон</th>
                    <th>Имя</th>
                    <th>Создан</th>
                    <th>Изменен</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
		<tr>
                    <td><a class="edit" href="{{ URL::asset('admin/admins/edit') }}/{{ $user->id }}"></a></td>
                    <td>
                        {{ Form::open(['route' => 'admin-manager-drop', 'method' => 'POST']) }}
                            {{ Form::hidden('_method', 'DELETE') }}
                            <input name="adm_id" type="hidden" value="{{ $user->id }}">
                            <input type="submit" class="drop" value="">
                        {{ Form::close() }}
                    
                    </td>
                    <td>{{ $user->login }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->phone }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ date('d/m/Y  H:i', strtotime($user->created_at)) }}</td>
                    <td>{{ date('d/m/Y  H:i', strtotime($user->updated_at)) }}</td>
		</tr>
		@endforeach 
            </tbody>
        </table>
        @endif
    </div>
    
</div>
@stop