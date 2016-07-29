<?php

try{
    $pdo = new PDO('mysql:dbname=gwent_laravel; host=localhost; charset=utf8', 'kirill', '123456', array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
}catch(PDOException $e){
    echo 'There is an error: '.$e -> getMessage();
    exit;
}


$q_user_online = $pdo -> query("SELECT id, user_online, updated_at FROM users WHERE user_online = 1");
$r_user_online = $q_user_online -> fetchAll(PDO::FETCH_ASSOC);

foreach($r_user_online as $key => $user){
    $time_diff = time() - strtotime($user['updated_at']);
    if($time_diff > 180){
        $online = 0;
        $q  = $pdo -> prepare("UPDATE users SET user_online = :online WHERE id = :id");
        $q -> bindParam(':id', $user['id']);
        $q -> bindParam(':online', $online);
        $q -> execute();
    }
}

$q_battle = $pdo -> query("SELECT id, created_at, fight_status FROM tbl_battles WHERE fight_status < 2");
$r_battle = $q_battle -> fetchAll(PDO::FETCH_ASSOC);

foreach($r_battle as $key => $battle){
    if(strtotime($battle['created_at']) < (time() - 60*30)){

        $q_battle_members = $pdo -> query("SELECT user_id, battle_id FROM tbl_battle_members WHERE battle_id = ".$battle['id']);
        $r_battle_members= $q_battle_members ->fetchAll(PDO::FETCH_ASSOC);

        $playing_status = 0;
        foreach ($r_battle_members as $i => $user){
            $q_user  = $pdo -> prepare("UPDATE users SET user_is_playing = :playing_status WHERE id = :id");
            $q_user -> bindParam(':id', $user['user_id']);
            $q_user -> bindParam(':playing_status', $playing_status);
            $q_user -> execute();
        }

        $q  = $pdo -> prepare("DELETE FROM tbl_battles WHERE id = :id");
        $q -> bindParam(':id', $battle['id']);
        $q -> execute();

        $q  = $pdo -> prepare("DELETE FROM tbl_battle_members WHERE battle_id = :id");
        $q -> bindParam(':id', $battle['id']);
        $q -> execute();
    }
}
?>