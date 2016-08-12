<?php
namespace App\Classes\Socket;

use App\BattleModel;
use App\BattleMembersModel;
use App\Classes\Socket\Base\BaseSocket;
use App\Http\Controllers\Site\SiteFunctionsController;
use App\Http\Controllers\Site\SiteGameController;
use Crypt;
use Ratchet\ConnectionInterface;

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

            case 'userReady':
                if($battle -> fight_status == 1){

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

                        }else{
                            $user = self::getUserData($battle->user_id_turn);
                            $players_turn = $battle->user_id_turn;
                        }

                        $result = ['message' => 'allUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user->login];

                        if($battle -> fight_status <= 1){                            
                            self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                        }

                        $battle -> fight_status = 2;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                            self::sendMessageToSelf($from, $result);
                        }
                        $battle -> save();
                    }
                }
                break;
            
            case 'userJoinedToRoom':
                if($battle -> fight_status <= 1){
                    if(count($battle_members) == $battle->players_quantity){
                        if($battle -> fight_status === 0){
                            $battle -> fight_status = 1; // Подключилось нужное количество пользователей
                            $battle -> save();
                        }

                        if($battle -> user_id_turn != 0){
                            $user_turn = self::getUserData($battle -> user_id_turn);
                        }else{
                            $user_turn = json_decode('{"login":""}');
                        }

                        $user = self::getUserData($msg->ident->userId); // Данные пользователя

                        $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user->login, 'login' => $user_turn->login, 'battleInfo' => $msg->ident->battleId];

                        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }
                
                if($battle -> fight_status == 2){
                    
                    if($battle -> user_id_turn != 0){
                        $user_turn = self::getUserData($battle -> user_id_turn);
                    }else{
                        $user_turn = json_decode('{"login":""}');
                    }
                    $result = ['message' => 'allUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user_turn->login];
                    self::sendMessageToSelf($from, $result);
                }
                break;

            
            case 'userMadeCardAction':
                if($battle -> fight_status == 2){
                    $card = json_decode(SiteGameController::getCardData($msg->card));//Получаем данные о карте
                    switch($msg->field){ //Порядковый номер поля
                        case 'meele':       $row = 0; break;
                        case 'range':       $row = 1; break;
                        case 'superRange':  $row = 2; break;
                        case 'sortable-cards-field-more': $row = 3; break;
                    }
                    
                    if((in_array($row, $card->action_row, true)) or ($msg->field == 'sortable-cards-field-more')){ //Если номер поля не подделал пользователь
                        //Данные о текущем пользователе
                        $current_battle_user_data  = \DB::table('tbl_battle_members')->select('id','user_id','battle_id', 'user_hand', 'user_discard')->where('battle_id','=',$msg->ident->battleId)->where('user_id','=',$msg->ident->userId)->get();

                        $user_hand = unserialize($current_battle_user_data[0]->user_hand); //рука пользователя
                        $user_discard = unserialize($current_battle_user_data[0]->user_discard); //Отбой пользователя
                        
                        if($battle->creator_id == $msg->ident->userId){ //Если пользователь является создателем стола (р1 -создатель)
                            $user_battle_field_identificator = 'p1';
                        }else{
                            $user_battle_field_identificator = 'p2';
                        }
                        if($msg->field == 'sortable-cards-field-more'){ //Если карта выкинута на поле спец карт
                            $user_battle_field_identificator = 'mid';
                        }
                        
                        foreach($card->actions as $i => $action){
                            if(($action->action == '12')||($action->action == '13')){ // Если карта кидается на поле противника
                                if($user_battle_field_identificator == 'p1'){
                                    $user_battle_field_identificator = 'p2';
                                }else{
                                    $user_battle_field_identificator = 'p1';
                                }
                            }
                        }
                        
                        $battle_field = unserialize($battle->battle_field);//Данные о поле битвы
                        
                        if($card->type == 'special'){//Если карта относится к специальным
                            $card_type = $card->type;
                            if($user_battle_field_identificator == 'mid'){//Если карта выкинута на поле спец карт
                                if(count($battle_field['mid']) > 6){//Если карт на поле спец карт больше 6ти
                                    $user_discard[] = $battle_field['mid'][0]; //Кидает первую карту в отбой
                                    unset($battle_field['mid'][0]); //Удаляем первую карту                                    
                                }
                                
                                $user_data = self::getUserData($current_battle_user_data[0]->user_id);//Узнаем логин пользователя
                                $card_data = SiteGameController::getCardData($card->id);
                                $battle_field['mid'][] = [json_decode($card_data), $user_data->login]; //Добавляем текущую карту на поле боя и её принадлежность пользователю
                                $battle_field['mid'] = array_values($battle_field[$user_battle_field_identificator]);
                            }else{
                                
                                foreach($card->actions as $i => $action){
                                    if(($action->action == '13')or($action->action == '24')or($action->action == '27')or($action->action == '29')){//Если логика карт предусматривает сразу уходить в отбой
                                        $user_discard[] = $card->id;
                                    }else{
                                        $battle_field[$user_battle_field_identificator][$row]['special'] = $card;
                                    }
                                }
                            }
                        }else{//Если карта относится к картам воинов
                            $battle_field[$user_battle_field_identificator][$row]['warrior'][] = $card;                            
                        }

                        $user_hand_card_count = count($user_hand); //Количество карт в колоде
                        for($i=0; $i<$user_hand_card_count; $i++){
                            if(Crypt::decrypt($user_hand[$i]['id']) == Crypt::decrypt($card->id)){ //Если id сходятся
                                unset($user_hand[$i]);//Сносим карту с руки
                                break;
                            }
                        }
                        
                        $user_hand = serialize(array_values($user_hand));
                        $user_discard = serialize(array_values($user_discard));                        
                        
                        \DB::table('tbl_battle_members')->where('id', '=', $current_battle_user_data[0]->id)->update(['user_hand' => $user_hand, 'user_discard' => $user_discard]);
                        
                        $battle->battle_field = serialize($battle_field);
                        $battle->save();
                        
                        $user_turn_id = self::changeUserTurn($msg->ident->battleId);
                        $user_turn = self::getUserData($user_turn_id);
                        $result = ['message' => 'userMadeAction', 'field_data' => $battle_field ,'battleInfo' => $msg->ident->battleId, 'login' => $user_turn->login];
                                
                        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }
                break;

        }
    }

    protected static function getUserData($user_id){
        $user = \DB::table('users')->select('id', 'login', 'user_current_deck')->where('id', '=', $user_id)->get();
        return $user[0];
    }
    
    protected static function buildCardDeck($deck){
        $result_array = [];
        foreach($deck as $key => $card_id){
            if(!empty($card_id)){
                $card_data = SiteGameController::getCardData($card_id);
                $result_array[] = $card_data;
            }
        }
        return $result_array;
    }
    

    protected static function changeUserTurn($current_battle_id){
        $current_user_turn = \DB::table('tbl_battles')->select('id','user_id_turn')->where('id', '=', $current_battle_id)->get();
        $next_user = \DB::table('tbl_battle_members')->select('battle_id', 'user_id')->where('battle_id', '=', $current_battle_id)->where('user_id', '!=', $current_user_turn[0]->user_id_turn)->get();
        BattleModel::where('id', '=', $current_battle_id)->update(['user_id_turn' => $next_user[0]->user_id]);
        return $next_user[0]->user_id;
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