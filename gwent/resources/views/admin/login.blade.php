<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/reset.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/within_style.css') }}">
    <title>Gwent Admin Main Page</title>
</head>

<body>
<div class="main-central-wrap">
    {{ Form::open(['route' => 'admin-login', 'method' => 'POST']) }}
	<table class="edition" style="margin: 15% auto 0 auto">
            <tr>
		<td>Логин:</td>
		<td><input name="username" type="text"></td>
            </tr>
            <tr>
		<td>Пароль:</td>
		<td><input name="password" type="password"></td>
            </tr>
            <tr>
            	<td class="tac" colspan="2"><button type="submit">Вход</button></td>
            </tr>
            <tr>
                <td class="tac" colspan="2">
                    @foreach ($errors->all() as $error)
                        <div class="errors" style="margin: auto;">{{ $error }}</div>
                    @endforeach
                </td>
            </tr>
	</table>
	{{ Form::close() }}
	
</div>
</body>

</html>