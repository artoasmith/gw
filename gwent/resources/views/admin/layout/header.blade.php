<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/reset.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::asset('css/within_style.css') }}">
    <script src="{{ URL::asset('js/jquery-2.min.js') }}"></script>
    <script src="{{ URL::asset('js/within.js') }}"></script>
    <title>Gwent Admin Main Page</title>
</head>

<body>
    
<header>
    <ul class="top-menu">
        <li><a href="/admin">Главная</a></li>
        <li>
            <a href="/admin/cards">Карты</a>
            <ul>
                <li><a href="/admin/cards/groups">Группы</a></li>
                <li><a href="/admin/cards/actions">Действия</a></li>
            </ul>
        </li>
        <li><a href="/admin/magic">Волшебство</a></li>
        <li><a href="/admin/users">Пользователи</a></li>
        <li><a href="/admin/admins">Администраторы</a></li>
        <li><a href="/admin/files">Файлы</a></li>
    </ul>
    
    <div class="admin-status-bar">
    <?php
    $user = Auth::user();
    if($user){
    ?>
        Вы зашли как, <strong>{{ $user -> login }}</strong>
        &nbsp;&nbsp;&nbsp;
        <a href="{{ URL::asset('admin/logout') }}" style="color: #fff">Выйти</a>
    <?php
    }
    ?>
    </div>
</header>
