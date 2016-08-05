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



        $battle_members = \DB::table('tbl_battle_members')->select('user_id','battle_id','user_ready', 'round_passed')->where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве

        SiteFunctionsController::updateUserInBattleConnection($msg->ident->userId);//Обновление пользовательского статуса online

        switch($msg->action){
            case 'userJoinedToRoom':

                if(count($battle_members) == $battle->players_quantity){
                    if($battle -> fight_status < 10){
                        $battle -> fight_status = 10; // Подключилось нужное количество пользователей
                        $battle -> save();
                    }

                    if($battle -> user_id_turn != 0){
                        $user_turn = self::getUserData($battle -> user_id_turn);
                    }else{
                        $user_turn = json_decode('{"login":""}');
                    }

                    $user = self::getUserData($msg->ident->userId); // Данные пользователя

                    $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user->login, 'userTurn' => $user_turn->login, 'battleInfo' => $msg->ident->battleId];

                    self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                    self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]); //Отправляем результат всем остальным

                }
                break;

            case 'userReady':
                var_dump($battle -> fight_status);
                if($battle -> fight_status == 10){

                    $ready_players_count = 0; //Количество игроков за столом готовых к игре
                    foreach ($battle_members as $key => $value){
                        if($value -> user_ready != 0){
                            $ready_players_count++;
                        }
                    }

                    if($ready_players_count == $battle->players_quantity){ //Если готовых к игре равное количество максимальному числу игроков за столом
                        if($battle -> user_id_turn == 0){ //Если игрок для хода не определен

                            $cursed_players = []; //Игроки фракции "Проклятые"
                            foreach ($battle_members as $key => $value){
                                $user = self::getUserData($value->user_id);
                                if($user -> user_current_deck == 'cursed'){
                                    $cursed_players[] = $user->id;
                                }
                            }

                            if(count($cursed_players) == 1){//Если за столом есть 1н игрок из фракции "Проклятые"
                                $players_turn = $cursed_players[0];
                            }else{
                                $players_turn = $battle_members[rand(0,$ready_players_count-1)] -> user_id;
                            }

                            $user = self::getUserData($players_turn);

                        }else {
                            $user = self::getUserData($battle->user_id_turn);
                            $players_turn = $battle->user_id_turn;
                        }

                        $result = ['message' => 'allUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user->login];

                        $battle -> fight_status = 101;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                            self::sendMessageToSelf($from, $result);
                        }
                        $battle -> save();

                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
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