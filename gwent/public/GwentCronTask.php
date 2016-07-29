<?php

try{
    $pdo = new PDO('mysql:dbname=gwent_laravel; host=localhost; charset=utf8', 'kirill', '123456', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}catch(PDOException $e){
    echo 'There is an error: '.$e -> getMessage();
    exit;
}


$q = $pdo -> query("SELECT id, user_online, updated_at FROM users WHERE user_online = 1");
$r = $q -> fetchAll(PDO::FETCH_ASSOC);

foreach($r as $key => $user){
    $time_diff = time() - strtotime($user['updated_at']);
    if($time_diff > 180){
        $online = 0;
        $q = $pdo -> prepare("UPDATE users SET user_online = :online WHERE id = :id");
        $q -> bindParam(':id', $user['id']);
        $q -> bindParam(':online', $online);
        $q -> execute();
    }
}

$q = $pdo -> query("SELECT id, created_at, fight_status FROM tbl_battles WHERE fight_status = 0");
$r = $q -> fetchAll(PDO::FETCH_ASSOC);

foreach($r as $key => $battle){
    if(strtotime($battle['created_at']) < (time() - 60*30)){
        $q = $pdo -> prepare("DELETE FROM tbl_battles WHERE id = :id");
        $q -> bindParam(':id', $battle['id']);
        $q -> execute();

        $q = $pdo -> prepare("DELETE FROM tbl_battle_members WHERE battle_id = :id");
        $q -> bindParam(':id', $battle['id']);
        $q -> execute();
    }
}
?>