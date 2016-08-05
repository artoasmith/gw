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


    protected static function getUserData($user_id){
        $user = \DB::table('users')->select('id', 'login', 'user_current_deck')->where('id', '=', $user_id)->get();
        return $user[0];
    }


    public function onMessage(ConnectionInterface $from, $msg){
        //Обработчик каждого сообщения

        $msg = json_decode($msg); // сообщение от пользователя arr[action, ident[battleId, UserId, Hash]]

        if(!isset($this->battles[$msg->ident->battleId])){
            $this->battles[$msg->ident->battleId] = new \SplObjectStorage;
        }

        if(!$this->battles[$msg->ident->battleId]->contains($from)){
            $this->battles[$msg->ident->battleId]->attach($from);
        }

        $battle = BattleModel::find($msg->ident->battleId); //Даные битвы

        $battle_members = \DB::table('tbl_battle_members')->select('user_id','battle_id','user_ready')->where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве

        SiteFunctionsController::updateUserInBattleConnection($msg->ident->userId);//Обновление пользовательского статуса online

        switch($msg->action){
            case 'userJoinedToRoom':
                if($battle -> fight_status < 2){

                    if(count($battle_members) == $battle->players_quantity){
                        $battle -> fight_status = 1; // Подключилось нужное количество пользователей
                        $battle -> save();

                        $user = self::getUserData($msg->ident->userId);

                        $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user->login, 'battleInfo' => $msg->ident->battleId];

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

                    if($ready_players_count == $battle->players_quantity){

                        $cursed_players = [];
                        foreach ($battle_members as $key => $value){
                            $user = self::getUserData($value->user_id);
                            if($user -> user_current_deck == 'cursed'){
                                $cursed_players[] = $user->id;
                            }
                        }

                        if(count($cursed_players) == 1){
                            $players_turn = $cursed_players[0];
                        }else{
                            $players_turn = $battle_members[rand(0,$ready_players_count-1)] -> user_id;
                        }

                        $user = self::getUserData($players_turn);

                        $battle -> fight_status = 2;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                        }
                        $battle -> save();

                        $result = ['message' => 'AllUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user->login];

                        self::sendMessageToSelf($from, $result);
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }
                break;

            case 'userPassedTurn':
                if($battle -> fight_status == 2) {

                    $battle_members_count = count($battle_members);
                    for($i=0; $i< $battle_members_count; $i++){

                        if($battle -> user_id_turn == $battle_members[$i] -> user_id){

                            if($i != $battle_members_count-1){
                                $next_user_turn = $battle_members[$i+1] -> user_id;
                            }else{
                                $next_user_turn = $battle_members[0] -> user_id;
                            }
                        }
                    }

                    if(!empty($next_user_turn)){
                        $battle -> user_id_turn = $next_user_turn;
                        $battle -> save();
                    }

                    $user = self::getUserData($next_user_turn);

                    $result = ['message' => 'turnChanged', 'battleInfo' => $msg->ident->battleId, 'login' => $user->login];

                    self::sendMessageToSelf($from, $result);
                    self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
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