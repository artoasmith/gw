<?php
namespace App\Classes\Socket;

use App\User;
use App\BattleModel;
use App\BattleMembersModel;
use App\Classes\Socket\Base\BaseSocket;
use Ratchet\ConnectionInterface;
use App\Http\Controllers\Site\SiteFunctionsController;

class JotSocket extends BaseSocket
{
    protected $clients;  //Соединения клиентов
    protected $battles;


    public function __construct(){
        $this->clients = new \SplObjectStorage;
    }


    public function onOpen(ConnectionInterface $conn){
        //Пользователь присоединяется к сессии

        $this->clients->attach($conn); //Добавление клиента

        echo 'New connection ('.$conn->resourceId.')'."\n\r";
    }


    public function onMessage(ConnectionInterface $from, $msg){
        //Обработчик каждого сообщения

        $msg = json_decode($msg);

        if(!isset($this->battles[$msg->ident->battleId])){
            $this->battles[$msg->ident->battleId] = new \SplObjectStorage;
        }

        if(!$this->battles[$msg->ident->battleId]->contains($from)){
            $this->battles[$msg->ident->battleId]->attach($from);
        }

        $battle = BattleModel::find($msg->ident->battleId);

        $battle_members = \DB::table('tbl_battle_members')->select('user_id','battle_id','user_ready')->where('battle_id', '=', $msg->ident->battleId)->get();

        SiteFunctionsController::updateUserInBattleConnection($msg->ident->userId);//Обновление пользовательского статуса online

        switch($msg->action){
            case 'userJoinedToRoom':
                if($battle -> fight_status < 2){

                    if(count($battle_members) == $battle->players_quantity){
                        $battle -> fight_status = 1; // Подключилось нужное количество пользователей
                        $battle -> save();

                        $user = \DB::table('users')->select('id','login')->where('id', '=', $msg->ident->userId)->get();

                        $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user[0] -> login, 'battleInfo' => $msg->ident->battleId];

                        self::sendMessageToSelf($from, $result);
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }
                break;

            case 'userReady':
                if($battle -> fight_status == 1){

                    $ready_players_count = 0;
                    foreach ($battle_members as $key => $value){
                        if($value -> user_ready != 0){
                            $ready_players_count++;
                        }
                    }

                    $players_turn = $battle_members[rand(0,$ready_players_count-1)] -> user_id;

                    $user = \DB::table('users')->select('id','login')->where('id', '=', $players_turn)->get();

                    if($ready_players_count == $battle->players_quantity){
                        $battle -> fight_status = 2;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                        }
                        $battle -> save();

                        $result = ['message' => 'AllUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user[0]->login];

                        self::sendMessageToSelf($from, $result);
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }
                break;

            case 'userPassedTurn':
                if($battle -> fight_status == 2) {
                    foreach($battle_members as $key => $value){
                        /*if($value -> user_id == $battle -> user_id_turn){
                            $next_turn_user = next($battle_members);
                            var_dump($next_turn_user);
                        }*/
                    }
                }
                break;
        }

    }


    protected static function sendMessageToOthers($from, $result, $battles){
        foreach ($battles as $client) {
            if ($client->resourceId != $from->resourceId) {
                $client->send(json_encode($result));
            }
        }
    }


    protected static function sendMessageToSelf($from, $message){
        $from->send(json_encode($message));
    }


    public function onClose(ConnectionInterface $conn){
        $this->clients->detach($conn);

        echo 'Connection '.$conn->resourceId.' has disconnected'."\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e){
        echo 'An error has occured: '.$e->getMessage()."\n";

        $conn -> close();
    }
}