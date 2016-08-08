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
                
            case 'userMadeCardAction':                
                $card_data = \DB::table('tbl_card')->select('id', 'card_type', 'allowed_rows', 'card_actions')->where('id', '=', $msg->cardData)->get();
                
                $user_member = \DB::table('tbl_battle_members')->select('user_id','user_hand')->where('user_id', '=', $msg->ident->userId)->get();
                $user_hand = unserialize($user_member[0]->user_hand);
                
                foreach($user_hand as $key => $value){
                    if($card_data[0]->id == $value['id']){
                        unset($user_hand[$key]);
                        break;
                    }
                }
                $user_hand = serialize(array_values($user_hand));
                
                
                switch($msg->field){
                    case '#sortable-oponent-cards-field-super-renge':
                        $field_relate_to_user = self::formUserBattleField($msg, '!=');
                        $row = 2;
                        break;                    
                    case '#sortable-oponent-cards-field-range':
                        $field_relate_to_user = self::formUserBattleField($msg, '!=');
                        $row = 1;
                        break;                    
                    case '#sortable-oponent-cards-field-meele':
                        $field_relate_to_user = self::formUserBattleField($msg, '!=');
                        $row = 0;
                        break;
                    
                    case '#sortable-user-cards-field-meele':
                        $field_relate_to_user = self::formUserBattleField($msg, '=');
                        $row = 0;
                        break;
                    case '#sortable-user-cards-field-range':
                        $field_relate_to_user = self::formUserBattleField($msg, '=');
                        $row = 1;
                        break;
                    case '#sortable-user-cards-field-super-renge':
                        $field_relate_to_user = self::formUserBattleField($msg, '=');
                        $row = 2;
                        break;
                    default:
                        $result = ['message' => 'error', 'error' => 'действие прервано. Перезагрузите сраницу (F5).','battleInfo' => $msg->ident->battleId, 'login' => $user->login];
                        self::sendMessageToSelf($from, $result);
                }
                
                if(!isset($result)){
                    $battle_field = unserialize($field_relate_to_user[0]->battle_field);
                    if($card_data[0]->card_type == 'special'){
                        if(in_array($row, unserialize($card_data[0]->allowed_rows), true)){
                            $battle_field[$row]['special'] = $card_data[0]->id;
                        }                        
                    }else{
                        if(in_array($row, unserialize($card_data[0]->allowed_rows), true)){
                            $battle_field[$row]['warrior'][] = $card_data[0]->id;
                        } 
                    }
                    
                    BattleMembersModel::where('user_id', '=', $msg->ident->userId)->update(['user_hand' => $user_hand]);
                    
                    BattleMembersModel::where('user_id', '=', $field_relate_to_user[0]->user_id)->update(['battle_field' => serialize($battle_field)]);
                    
                }
                break;

        }

    }
    
    
    protected static function formUserBattleField($msg, $equal){
        if($equal == '!='){
            return \DB::table('tbl_battle_members')->select('id','user_id','battle_id','battle_field')->where('battle_id', '=', $msg->ident->battleId)->where('user_id', '!=', $msg->ident->userId)->get();
        }
        if($equal == '='){
            return \DB::table('tbl_battle_members')->select('id','user_id','battle_id','battle_field')->where('battle_id', '=', $msg->ident->battleId)->where('user_id', '=', $msg->ident->userId)->get();
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