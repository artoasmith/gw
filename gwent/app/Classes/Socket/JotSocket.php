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

    //Socket actions
    public function onOpen(ConnectionInterface $conn){
        //Пользователь присоединяется к сессии
        $this->clients->attach($conn); //Добавление клиента
        echo 'New connection ('.$conn->resourceId.')'."\n\r";
    }

    public function onMessage(ConnectionInterface $from, $msg){
        //Обработчик каждого сообщения

        $msg = json_decode($msg); // сообщение от пользователя arr[action, ident[battleId, UserId, Hash]]
        var_dump($msg);

        if(!isset($this->battles[$msg->ident->battleId])){
            $this->battles[$msg->ident->battleId] = new \SplObjectStorage;
        }

        if(!$this->battles[$msg->ident->battleId]->contains($from)){
            $this->battles[$msg->ident->battleId]->attach($from);
        }

        $battle = BattleModel::find($msg->ident->battleId); //Даные битвы

        $battle_members = BattleMembersModel::where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве

        foreach($battle_members as $key => $value){
            $current_user = \DB::table('users')->select('id','login','user_current_deck')->where('id','=',$value->user_id)->get();
            
            $user_identificator = ($value->user_id == $battle->creator_id) ? 'p1' : 'p2';
            
            if($value->user_id == $msg->ident->userId){
                $user_array = [
                    'id'            => $value->user_id,
                    'login'         => $current_user[0]->login,
                    'player'        => $user_identificator,
                    'user_deck'     => unserialize($value->user_deck),
                    'user_hand'     => unserialize($value->user_hand),
                    'user_discard'  => unserialize($value->user_discard),
                    'current_deck'  => $current_user[0]->user_current_deck,
                    'card_source'   => $value->card_source,
                    'round_passed'  => $value->round_passed,
                    'battle_member_id' => $value->id
                ];
            }else{
                $opponent_array = [
                    'id'            => $value->user_id,
                    'login'         => $current_user[0]->login,
                    'player'        => $user_identificator,
                    'user_deck'     => unserialize($value->user_deck),
                    'user_hand'     => unserialize($value->user_hand),
                    'user_discard'  => unserialize($value->user_discard),
                    'current_deck'  => $current_user[0]->user_current_deck,
                    'card_source'   => $value->card_source,
                    'round_passed'  => $value->round_passed,
                    'battle_member_id' => $value->id
                ];
            }
        }
        
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

                            //Игроки фракции "Проклятые"
                            $cursed_players = [];
                            if($user_array['current_deck'] == 'cursed') $cursed_players[] = ['id'=>$user_array['id'], 'login'=> $user_array['login']];
                            if($opponent_array['current_deck'] == 'cursed') $cursed_players[] = ['id'=>$opponent_array['id'], 'login'=> $opponent_array['login']];

                            if(count($cursed_players) == 1){//Если за столом есть 1н игрок из фракции "Проклятые"
                                $players_turn = $cursed_players[0]['id'];
                                $user = $cursed_players[0]['login'];
                            }else{
                                $rand = rand(0,1);
                                if($rand == 0){
                                    $players_turn = $user_array['id'];
                                    $user = $user_array['login'];
                                }else{
                                    $players_turn = $opponent_array['id'];
                                    $user = $opponent_array['login'];
                                }
                            }

                        }else{
                            $players_turn = $battle->user_id_turn;
                            if($user_array['id'] == $battle->user_id_turn){
                                $user = $user_array['login'];
                            }else{
                                $user = $opponent_array['login'];
                            }
                        }

                        $result = ['message'=>'allUsersAreReady', 'cardSource'=>$opponent_array['card_source'], 'battleInfo'=>$msg->ident->battleId, 'login'=>$user];

                        if($battle -> fight_status <= 1){
                            self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                        }

                        $battle -> fight_status = 2;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                            $result = ['message'=>'allUsersAreReady', 'cardSource'=>$user_array['card_source'], 'battleInfo'=>$msg->ident->battleId, 'login'=>$user];
                            self::sendMessageToSelf($from, $result);
                        }
                        $battle -> save();
                    }
                }
                break;

            case 'userJoinedToRoom':
                
                if($battle -> user_id_turn != 0){
                    if($battle -> user_id_turn == $user_array['id']){
                        $user_turn = $user_array['login'];
                    }else{
                        $user_turn = $opponent_array['login'];
                    }
                }else{
                    $user_turn = '';
                }
                
                if($battle -> fight_status <= 1){
                    if(count($battle_members) == $battle->players_quantity){
                        if($battle -> fight_status === 0){
                           $battle -> fight_status = 1; // Подключилось нужное количество пользователей
                           $battle -> save();
                        }

                        $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user_array['login'], 'login' => $user_turn, 'battleInfo' => $msg->ident->battleId];

                        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }

                if($battle -> fight_status == 2){
                    $result = ['message' => 'allUsersAreReady', 'cardSource'=>$user_array['card_source'], 'battleInfo' => $msg->ident->battleId, 'login' => $user_turn];
                    self::sendMessageToSelf($from, $result);
                }
                break;

            case 'getOwnBattleFieldData':
                $battle_field = unserialize($battle->battle_field);//Данные о поле битвы
                $own_cards = [];
                foreach($battle_field as $field => $rows){
                    if($field != 'mid'){
                        foreach($rows as $row => $cards){
                            foreach($cards['warrior'] as $i => $card_data){
                                if($card_data['login'] == $user_array['login']){
                                    $own_cards[$field][$row][] = $card_data['card']->id;
                                }
                            }
                        }
                    }
                }
                $result = ['message' => 'ownCardsData', 'battleData' => $own_cards, 'battleInfo' => $msg->ident->battleId];
                self::sendMessageToSelf($from, $result);
                break;

            case 'userMadeCardAction':
                echo date('Y-m-d H:i:s')."\n";
                if($battle -> fight_status == 2){
                    //Данные о текущем пользователе

                    $battle_field = unserialize($battle->battle_field);//Данные о поле битвы

                    //Если пользователь не спасовал
                    if($user_array['round_passed'] == 0){
                        //определение очереди хода
                        $user_turn = $opponent_array['login'];
                        $user_turn_id = $opponent_array['id'];
                        $card_source = 'hand';

                        $card = json_decode(SiteGameController::getCardData($msg->card));//Получаем данные о карте

                        $field_row = self::strRowToInt($msg->field);//Поле в которое ложится карта

                        //Если номер поля не подделал пользователь
                        if((in_array($field_row, $card->action_row, true)) || ($msg->field == 'sortable-cards-field-more')){

                            if($battle->creator_id == $msg->ident->userId){ //Если пользователь является создателем стола (р1 -создатель)
                                $user_battle_field_identificator = 'p1';
                                $oponent_battle_field_identificator = 'p2';
                            }else{
                                $user_battle_field_identificator = 'p2';
                                $oponent_battle_field_identificator = 'p1';
                            }
                            if($msg->field == 'sortable-cards-field-more'){ //Если карта выкинута на поле спец карт
                                $user_battle_field_identificator = 'mid';
                            }

                            foreach($card->actions as $i => $action){
                                // Если карта кидается на поле противника
                                if(($action->action == '12')||(($action->action == '13')&&($card->type == 'special'))||($action->action == '26')){
                                    if($user_battle_field_identificator == 'p1'){
                                        $user_battle_field_identificator = 'p2';
                                        $oponent_battle_field_identificator = 'p1';
                                    }else{
                                        $user_battle_field_identificator = 'p1';
                                        $oponent_battle_field_identificator = 'p2';
                                    }
                                }
                            }

                            //Если карта относится к специальным
                            if($card->type == 'special'){

                                //Если карта выкинута на поле спец карт
                                if($user_battle_field_identificator == 'mid'){
                                    $battle_field['mid'][] = ['card' => $card, 'strength' => $card->strength, 'login' => $user_array['login']];
                                    var_dump(count($battle_field['mid']));
                                    //Если карт на поле спец карт больше 6ти
                                    if(count($battle_field['mid']) > 6){
                                        //Кидает первую карту в отбой
                                        if($user_array['login'] == $battle_field['mid'][0]['login']){
                                            $user_array['user_discard'][] = $battle_field['mid'][0]['card'];
                                        }else{
                                            $opponent_array['user_discard'][] = $battle_field['mid'][0]['card'];
                                        }
                                        //Удаляем первую карту
                                        unset($battle_field['mid'][0]);
                                        var_dump('---yeap---');
                                    }
                                    //Добавляем текущую карту на поле боя и её принадлежность пользователю
                                    
                                    $battle_field['mid'] = array_values($battle_field['mid']);
                                    
                                    var_dump($battle_field['mid']);

                                }else{

                                    //Если логика карт предусматривает сразу уходить в отбой
                                    foreach($card->actions as $i => $action){
                                        if( (($action->action == '13')&&($card->type == 'special'))||($action->action == '24')||($action->action == '27')||($action->action == '29')){
                                            $user_array['user_discard'][] = $card;
                                        }else{
                                            //Еcли в ряду уже есть спец карта
                                            if(!empty($battle_field[$user_battle_field_identificator][$field_row]['special'])){
                                                if($battle_field[$user_battle_field_identificator][$field_row]['special']['login'] == $user_array['login']){
                                                    $user_array['user_discard'][] = $battle_field[$user_battle_field_identificator][$field_row]['special']['card'];
                                                }else{
                                                    $opponent_array['user_discard'][] = $battle_field[$user_battle_field_identificator][$field_row]['special']['card'];
                                                }
                                                
                                            }
                                            $battle_field[$user_battle_field_identificator][$field_row]['special'] = ['card' => $card, 'strength' => $card->strength, 'login' => $user_array['login']];
                                        }
                                    }
                                }
                            //Если карта относится к картам воинов 
                            }else{
                                $battle_field[$user_battle_field_identificator][$field_row]['warrior'][] = ['card'=>  get_object_vars($card), 'strength'=>$card->strength, 'login' => $user_array['login']];
                            }
                            
                            //Перебор действий карты
                            switch($msg->source){
                                case 'hand':
                                    $user_array['user_hand'] = self::dropCardFromDeck($user_array['user_hand'], $card);
                                    break;
                                case 'deck':
                                    $user_array['user_deck'] = self::dropCardFromDeck($user_array['user_deck'], $card);
                                    break;
                                case 'discard':
                                    $user_array['user_discard'] = self::dropCardFromDeck($user_array['user_discard'], $card);
                                    break;
                            }echo __LINE__."\n";

                            foreach($card->actions as $action_iter => $action_data){
                                //ШПИОН
                                if($action_data->action == '12'){
                                    $deck_card_count = count($user_array['user_deck']);

                                    $n = ($deck_card_count >= $action_data->CAspy_get_cards_num) ? $action_data->CAspy_get_cards_num : $deck_card_count;
                                    for($i=0; $i<$n; $i++){

                                        $rand_item = rand(0, $deck_card_count-1);
                                        $random_card = $user_array['user_deck'][$rand_item];
                                        $user_array['user_hand'][] = $random_card;

                                        unset($user_array['user_deck'][$rand_item]);

                                        $user_array['user_deck'] = array_values($user_array['user_deck']);
                                        $deck_card_count = count($user_array['user_deck']);
                                    }
                                }echo __LINE__."\n";
                                //END OF ШПИОН

                                //УБИЙЦА
                                if($action_data->action == '13'){
                                    //Может ли бить своих
                                    $players = ( (isset($action_data->CAkiller_atackTeamate)) && ($action_data->CAkiller_atackTeamate == 1) ) ? $players = ['p1', 'p2'] : [$oponent_battle_field_identificator];

                                    //наносит удат по группе
                                    if( (isset($action_data->CAkiller_groupOrSingle)) && ($action_data->CAkiller_groupOrSingle != 0)){
                                        $groups = $action_data->CAkiller_groupOrSingle;
                                    }else{
                                        $groups = [];
                                    }

                                    $cards_can_be_destroyed = [];

                                    //Для каждого поля битвы
                                    foreach ($battle_field as $fields => $rows){
                                        //Если поле в находится в разрешенных$ для убийства
                                        if(in_array($fields, $players)){

                                            $enemy_player = $opponent_array['player'];

                                            //Для каждого ряда
                                            $rows_strength = 0; //Сумарная сила выбраных рядов
                                            $max_strenght = 0;  // максимальная сила карты
                                            $min_strenght = 999;// минимальная сила карты
                                            $card_strength_set = []; //набор силы карты для выбора случйного значения силы
                                            //Узнаем необходимые значения в массиве поля битвы
                                            foreach($rows as $row => $cards){
                                                //Если ряд находится в области действия карты-убийцы
                                                if(in_array($row, $action_data->CAkiller_ActionRow)){
                                                    //Если данное поле является полем противника
                                                    foreach($battle_field[$enemy_player][$row]['warrior'] as $i => $card_data){
                                                        if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars ($card_data['card']);
                                                        
                                                        $rows_strength += $card_data['strength'];//Сумарная сила выбраных рядов

                                                        $can_kill_this_card = 1; //Имунитет к убийству 1 - не имеет иммунитет; 0 - не имеет
                                                        foreach($card_data['card']['actions'] as $j => $action_immune){
                                                            if($action_immune->action == '18'){
                                                                $can_kill_this_card = 0;
                                                            }
                                                        }
                                                        //Атакуящая карта игнорирует иммунитет к убийству
                                                        if( (isset($action_data->CAkiller_ignoreKillImmunity)) && ($action_data->CAkiller_ignoreKillImmunity != 0) ){
                                                            $can_kill_this_card = 1;
                                                        }
                                                        if($can_kill_this_card == 1){
                                                            $max_strenght = ($max_strenght < $card_data['strength']) ? $card_data['strength'] : $max_strenght;// максимальная сила карты
                                                            $min_strenght = ($min_strenght > $card_data['strength']) ? $card_data['strength'] : $min_strenght;// минимальная сила карты
                                                            $card_strength_set[] = $card_data['strength'];
                                                        }

                                                    }
                                                }
                                            }
                                            $card_strength_set = array_values(array_unique($card_strength_set));

                                            //Качество убиваемой карты
                                            switch($action_data->CAkiller_killedQuality_Selector){
                                                case '0': $card_strength_to_kill = $min_strenght; break;//Самую слабую
                                                case '1': $card_strength_to_kill = $max_strenght; break;//Самую сильную
                                                case '2': $random = rand(0, count($card_strength_set)-1); $card_strength_to_kill = $card_strength_set[$random]; break;
                                            }

                                            foreach($rows as $row => $cards){
                                                //Если данный ряд доступен для убийства
                                                if(in_array($row, $action_data->CAkiller_ActionRow)){
                                                    //Порог силы воинов противника для совершения убийства
                                                    $action_data->CAkiller_enemyStrenghtLimitToKill = ($action_data->CAkiller_enemyStrenghtLimitToKill == 0) ? 999 : $action_data->CAkiller_enemyStrenghtLimitToKill;

                                                    //Нужное для совершения убийства количество силы в ряду
                                                    $allow_to_kill_by_force_amount = 1;

                                                    if($action_data->CAkiller_recomendedTeamateForceAmount_OnOff != 0){//Если не выкл
                                                        switch($action_data->CAkiller_recomendedTeamateForceAmount_Selector){
                                                            case '0':   //Больше указаного значения
                                                                $allow_to_kill_by_force_amount = ($action_data->CAkiller_recomendedTeamateForceAmount_OnOff < $rows_strength) ? 1 : 0; break;
                                                            case '1':   //Меньше указанного значения
                                                                $allow_to_kill_by_force_amount = ($action_data->CAkiller_recomendedTeamateForceAmount_OnOff > $rows_strength) ? 1 : 0; break;
                                                            case '2':   //Равно указанному значению
                                                                $allow_to_kill_by_force_amount = ($action_data->CAkiller_recomendedTeamateForceAmount_OnOff ==$rows_strength) ? 1 : 0; break;
                                                        }
                                                    }

                                                    foreach($cards['warrior'] as $card_iterator => $card_data){
                                                        if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars ($card_data['card']);
                                                        
                                                        if($card_data['strength'] < $action_data->CAkiller_enemyStrenghtLimitToKill){
                                                            //Игнор к иммунитету
                                                            $allow_to_kill_by_immune = 1; //Разрешено убивать карту т.к. иммунитет присутствует

                                                            foreach($card_data['card']['actions'] as $card_action_i => $card_current_action){
                                                                if($card_current_action->action == '18'){
                                                                    $allow_to_kill_by_immune = 0;
                                                                }
                                                            }

                                                            if( (isset($action_data->CAkiller_ignoreKillImmunity)) && ($action_data->CAkiller_ignoreKillImmunity != 0) ){
                                                                $allow_to_kill_by_immune = 1;
                                                            }

                                                            //Совершаем убийство карты по указанным выше параметрам
                                                            if( ($allow_to_kill_by_force_amount == 1) && ($card_data['strength'] == $card_strength_to_kill) && ($allow_to_kill_by_immune == 1) ){
                                                                //Массив карт которые возможно уничтожить
                                                                if(!empty($groups)){
                                                                    foreach($card_data['card']['groups'] as $groups_ident => $group_id){
                                                                        if(in_array($group_id, $groups)){
                                                                            $cards_can_be_destroyed[] = ['player' => $fields, 'card_id' => $card_data['card']['id']];
                                                                        }
                                                                    }
                                                                }else{
                                                                    $cards_can_be_destroyed[] = ['player' => $fields, 'card_id' => $card_data['card']['id']];
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }echo __LINE__."\n";

                                    $cards_to_destroy = [];
                                    if( (isset($action_data->CAkiller_killAllOrSingle)) && ($action_data->CAkiller_killAllOrSingle == 0) ){
                                        if(!empty($cards_can_be_destroyed)){
                                            $index = rand(0, count($cards_can_be_destroyed)-1);
                                            $cards_to_destroy[] = $cards_can_be_destroyed[$index];
                                        }
                                    }else{
                                        $cards_to_destroy = $cards_can_be_destroyed;
                                    }

                                    $cards_to_destroy_count = count($cards_to_destroy);

                                    for($i=0; $i<$cards_to_destroy_count; $i++){
                                        foreach($battle_field[$cards_to_destroy[$i]['player']] as $rows => $cards){
                                            foreach($cards['warrior'] as $card_iterator => $card_data){
                                                if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars ($card_data['card']);
                                            
                                                if($card_data['card']['id'] == $cards_to_destroy[$i]['card_id']){

                                                    if($user_array['player'] == $cards_to_destroy[$i]['player']){
                                                        $user_array['user_discard'][] = $card_data['card'];
                                                    }else{
                                                        $opponent_array['user_discard'][] = $card_data['card'];
                                                    }
                                                    unset($battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior'][$card_iterator]);
                                                    $battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior'] = array_values($battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior']);

                                                }
                                            }
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF УБИЙЦА

                                //РАЗВЕДЧИК
                                if($action_data->action == '15'){
                                    $deck_card_count = count($user_array['user_deck']);
                                    if($deck_card_count > 0){
                                        $rand_item = rand(0, $deck_card_count-1);
                                        $random_card = $user_array['user_deck'][$rand_item];
                                        $user_array['user_hand'][] = $random_card;
                                        unset($user_array['user_deck'][$rand_item]);

                                        $user_array['user_deck'] = array_values($user_array['user_deck']);
                                    }
                                }echo __LINE__."\n";
                                //END OF РАЗВЕДЧИК

                                //CТРАШНЫЙ
                                if($action_data->action == '21'){
                                    $players = ($action_data->CAfear_actionTeamate == 1) ? ['p1', 'p2'] : [$opponent_array['player']]; //Карта действует на всех или только на противника
                                    foreach($players as $field){
                                        foreach($battle_field[$field] as $row => $cards){//перебираем карты в рядах
                                            if(in_array($row, $action_data->CAfear_ActionRow)){//Если данный ряд присутствует в области действия карты "Страшный"
                                                if(!empty($cards['special'])){
                                                    //Проверяем присутсвует ли карта "Исцеление" в текущем ряду
                                                    foreach($cards['special']['card']->actions as $i => $action){
                                                        if($action->action == '25'){
                                                            if($user_array['player'] == $field){//Кидаем карту "Исцеление" в отбой
                                                                $user_array['user_discard'][] = $cards['special']['card'];
                                                            }else{
                                                                $opponent_array['user_discard'][] = $cards['special']['card'];
                                                            }
                                                            $battle_field[$field][$row]['special'] = '';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF CТРАШНЫЙ

                                //ИСЦЕЛЕНИЕ
                                if($action_data->action == '25'){
                                    foreach($battle_field['mid'] as $i => $card_data){
                                        foreach($card_data['card']->actions as $action_iterrator => $action){
                                            if(in_array($field_row, $action->CAfear_ActionRow)){
                                                if($user_array['login'] == $card_data['login']){
                                                    $user_array['user_discard'][] = $card_data['card'];
                                                }else{
                                                    $opponent_array['user_discard'][] = $card_data['card'];
                                                }
                                                unset($battle_field['mid'][$i]);
                                            }
                                        }
                                    }
                                    $battle_field['mid'] = array_values($battle_field['mid']);
                                }echo __LINE__."\n";
                                //END OF ИСЦЕЛЕНИЕ

                                //ОДУРМАНИВАНИЕ
                                if($action_data->action == '23'){
                                    $cards_can_be_obscured = [];
                                    $min_strength = 999;
                                    $max_strength = 0;

                                    foreach($battle_field[$opponent_array['player']] as $row => $cards){
                                        if(in_array($row, $action_data->CAobscure_ActionRow)){
                                            foreach($cards['warrior'] as $i => $card_data){
                                                if($card_data['strength'] <= $action_data->CAobscure_maxCardStrong){
                                                    $max_strength = ($card_data['strength'] > $max_strength) ? $card_data['strength'] : $max_strength;
                                                    $min_strength = ($card_data['strength'] < $min_strength) ? $card_data['strength'] : $max_strength;
                                                    $cards_can_be_obscured[] = ['card'=>$card_data['card'], 'strength' => $card_data['strength'], 'row'=>$row];
                                                }
                                            }
                                        }
                                    }
                                    if($min_strength < 1) $min_strength = 1;

                                    if(!empty($cards_can_be_obscured)){
                                        switch($action_data->CAobscure_strenghtOfCardToObscure){
                                            case '0': $card_strength_to_obscure = $min_strength; break;//Самую слабую
                                            case '1': $card_strength_to_obscure = $max_strength; break;//Самую сильную
                                            case '2':                                        
                                                $random = rand(0, count($cards_can_be_obscured)-1);
                                                $card_strength_to_obscure = $cards_can_be_obscured[$random]['strength'];
                                                break;
                                        }
                                    }

                                    $cards_to_obscure = [];
                                    if(!empty($cards_can_be_obscured)){
                                        for($i=0; $i<$action_data->CAobscure_quantityOfCardToObscure; $i++){
                                            for($j=0; $j<count($cards_can_be_obscured); $j++){
                                                if($card_strength_to_obscure == $cards_can_be_obscured[$j]['strength']){
                                                    $cards_to_obscure[] = $cards_can_be_obscured[$j];
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    for($i=0; $i<count($cards_to_obscure); $i++){
                                        foreach($battle_field[$opponent_array['player']][$cards_to_obscure[$i]['row']]['warrior'] as $j => $card_data){
                                            if(Crypt::decrypt($cards_to_obscure[$i]['card']->id) == Crypt::decrypt($card_data['card']->id)){
                                                $battle_field[$user_array['player']][$cards_to_obscure[$i]['row']]['warrior'][] = $card_data;
                                                unset($battle_field[$opponent_array['player']][$cards_to_obscure[$i]['row']]['warrior'][$j]);
                                                $battle_field[$opponent_array['player']][$cards_to_obscure[$i]['row']]['warrior'] = array_values($battle_field[$opponent_array['player']][$cards_to_obscure[$i]['row']]['warrior']);
                                                break;
                                            }
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF ОДУРМАНИВАНИЕ

                                //ПЕРЕГРУППИРОВКА
                                if($action_data->action == '24'){
                                    foreach($battle_field[$msg->player][$field_row]['warrior'] as $i => $card_data){
                                        if($card_data['card']->id == $msg->retrieve){
                                            unset($battle_field[$msg->player][$field_row]['warrior'][$i]);
                                            $battle_field[$msg->player][$field_row]['warrior'] = array_values($battle_field[$msg->player][$field_row]['warrior']);
                                            $user_array['user_hand'][] = get_object_vars($card_data['card']);
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF ПЕРЕГРУППИРОВКА
                                
                                //ПРИЗЫВ
                                if($action_data->action == '27'){
                                    $allow_change_turn = 0;
                                    if(!empty($user_array['user_deck'])){
                                        foreach($user_array['user_deck'] as $i => $card_data){
                                            if(!is_array($card_data)){
                                                $card_data = get_object_vars($card_data);
                                            }
                                            if($card_data['type'] != 'special'){
                                                $allow_change_turn = 1;
                                            }
                                        }
                                    }

                                    if($allow_change_turn == 1){
                                        $user_turn = $user_array['login'];
                                        $user_turn_id = $user_array['id'];
                                        $card_source = 'deck';
                                    }                                    
                                }echo __LINE__."\n";
                                //END OF ПРИЗЫВ
                                
                                //ЛЕКАРЬ
                                if($action_data->action == '29'){
                                    $allow_change_turn = 0;
                                    if(!empty($user_array['user_discard'])){
                                        foreach($user_array['user_discard'] as $i => $card_data){
                                            if(!is_array($card_data)){
                                                $card_data = get_object_vars($card_data);
                                            }
                                            if($card_data['type'] != 'special'){
                                                $allow_change_turn = 1;
                                            }
                                        }
                                    }
                                    
                                    if($allow_change_turn == 1){
                                        $user_turn = $user_array['login'];
                                        $user_turn_id = $user_array['id'];
                                        $card_source = 'discard';
                                    }
                                }echo __LINE__."\n";
                                //END OF ЛЕКАРЬ
                                
                                //ПЕЧАЛЬ
                                if($action_data->action == '26'){
                                    if($action_data->CAsorrow_actionTeamate == 0){
                                        $action_fields = [$opponent_array['player']];
                                    }else{
                                        $action_fields = ['p1', 'p2'];
                                    }
                                    
                                    if($action_data->CAsorrow_actionToAll == 0){
                                        $action_rows = [$field_row];
                                    }else{
                                        $action_rows = $action_data->CAsorrow_ActionRow;
                                    }
                                    
                                    foreach($action_fields as $i => $player){
                                        foreach($action_rows as $j => $rows){
                                            if(!empty($battle_field[$player][$rows]['special'])){
                                                foreach($battle_field[$player][$rows]['special']['card']->actions as $k => $action){
                                                    if($action->action == '28'){
                                                        if($player == $user_array['player']){
                                                            $user_array['user_discard'][] = $battle_field[$player][$rows]['special']['card'];
                                                        }else{
                                                            $opponent_array['user_discard'][] = $battle_field[$player][$rows]['special']['card'];
                                                        }
                                                        $battle_field[$player][$rows]['special'] = '';
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF ПЕЧАЛЬ
                                
                                //ПОВЕЛИТЕЛЬ
                                if($action_data->action == '22'){
                                    $source_decks = [];
                                    foreach($action_data->CAmasder_cardSource as $i => $current_card_source){
                                        switch($current_card_source){
                                            case 'hand':    $source_decks[] = 'user_hand'; break;
                                            case 'passed':  $source_decks[] = 'user_discard'; break;
                                            default:        $source_decks[] = 'user_deck';
                                        }
                                    }
                                    
                                    $card_counter = 0;
                                    $cards_to_add = [];
                                    for($i=0; $i<count($source_decks); $i++){
                                        foreach($user_array[$source_decks[$i]] as $card_iter => $card_data){
                                            $allow_to_add = false;
                                            if($card_data['strength'] <= $action_data->CAmaster_maxCardsStrenght){
                                                if(!empty($card_data['groups'])){
                                                    foreach($card_data['groups'] as $group_iter => $group_id){
                                                        if(in_array($group_id, $action_data->CAmaster_group)){
                                                            $allow_to_add = true;
                                                            $card_counter++;
                                                        }
                                                    }
                                                }
                                            }
                                            if( ($card_counter < $action_data->CAmaster_maxCardsSummon) && ($allow_to_add) ){
                                                $cards_to_add[$source_decks[$i]][] = [
                                                    'card'      => $user_array[$source_decks[$i]][$card_iter],
                                                    'strength'  => $user_array[$source_decks[$i]][$card_iter]['strength'],
                                                    'login'     => $user_array['login']
                                                ];
                                            }
                                        }
                                    }
                                    
                                    foreach($cards_to_add as $deck => $cards){
                                        foreach($cards as $card_iter =>  $card_data){
                                            $action_rows_count = count($card_data['card']['action_row']);
                                            if($action_rows_count > 1){
                                                $action_row = $card_data['card']['action_row'][$action_rows_count-1];
                                            }else{
                                                $action_row = $card_data['card']['action_row'][0];
                                            }
                                            
                                            $battle_field[$user_array['player']][$action_row]['warrior'][] = $card_data;
                                            
                                            foreach($user_array[$deck] as $deck_iter => $card_in_deck){
                                                if(Crypt::decrypt($card_data['card']['id']) == Crypt::decrypt($card_in_deck['id'])){
                                                    unset($user_array[$deck][$deck_iter]);
                                                }
                                            }
                                            $user_array[$deck] = array_values($user_array[$deck]);
                                        }
                                    }
                                }echo __LINE__."\n";
                                //END OF ПОВЕЛИТЕЛЬ
                            }
                            //END OF Перебор действий карты
                        }

                        $battle_field = self::recalculateStrengthByMid($battle_field, $user_array, $opponent_array);
                        
                        if(count($user_array['user_hand']) == 0){//Если у пользлвателя закончились карты на руках - делаем ему автопас
                            \DB::table('tbl_battle_members')->where('id', '=', $msg->ident->battleId)->update(['round_passed' => '1']);
                        }echo __LINE__."\n";

                        $user_discard_count = count($user_array['user_discard']);
                        $user_deck_count = count($user_array['user_deck']);
                        $user_hand_count = count($user_array['user_hand']);

                        $oponent_discard_count = count($opponent_array['user_discard']);
                        $oponent_deck_count = count($opponent_array['user_deck']);

                        $user_array['user_deck'] = serialize(array_values($user_array['user_deck']));
                        $user_array['user_hand'] = serialize(array_values($user_array['user_hand']));
                        $user_array['user_discard'] = serialize(array_values($user_array['user_discard']));

                        $opponent_array['user_deck'] = serialize(array_values($opponent_array['user_deck']));
                        $opponent_array['user_hand'] = serialize(array_values($opponent_array['user_hand']));
                        $opponent_array['user_discard'] = serialize(array_values($opponent_array['user_discard']));
                        //Сохраняем руку, колоду и отбой опльзователя
                        \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_member_id'])->update(['user_deck'=>$user_array['user_deck'], 'user_hand' => $user_array['user_hand'], 'user_discard' => $user_array['user_discard'], 'card_source'=>$card_source]);
                        \DB::table('tbl_battle_members')->where('id', '=', $opponent_array['battle_member_id'])->update(['user_deck'=>$opponent_array['user_deck'], 'user_hand' => $opponent_array['user_hand'], 'user_discard' => $opponent_array['user_discard']]);
                        //Сохраняем поле битвы
                        $battle->battle_field = serialize($battle_field);
                        $battle->user_id_turn = $user_turn_id;
                        $battle->save();

                        /*
                         * Выход:
                         * message = userMadeAction -> Пользователь сделал действие
                         * field_data -> карты на поле
                         * user_hand -> карты руки пользователя
                         * counts [user_discard_count, opon_discard_count, opon_deck_count]
                         */

                        $result = [
                            'message'       => 'userMadeAction',
                            'field_data'    => $battle_field,
                            'user_hand'     => unserialize($user_array['user_hand']),
                            'user_deck'     => unserialize($user_array['user_deck']),
                            'user_discard'  => unserialize($user_array['user_discard']),
                            'counts'        => [
                                'user_deck'    => $user_deck_count,
                                'user_discard' => $user_discard_count,
                                'opon_discard' => $oponent_discard_count,
                                'opon_deck'    => $oponent_deck_count
                            ],
                            'battleInfo'    => $msg->ident->battleId,
                            'login'         => $user_turn,
                            'cardSource'    => $card_source
                        ];

                        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                        $result = [
                            'message'       => 'userMadeAction',
                            'field_data'    => $battle_field,
                            'user_discard'  => unserialize($opponent_array['user_discard']),
                            'counts'        => [
                                'user_deck'    => $oponent_deck_count,
                                'user_discard' => $oponent_discard_count,
                                'opon_discard' => $user_discard_count,
                                'opon_deck'    => $user_deck_count
                            ],
                            'battleInfo'    => $msg->ident->battleId,
                            'login'         => $user_turn
                        ];
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }else{
                        //roud_passed != 0;
                    }
                }
                break;
        }
    }


    public function onClose(ConnectionInterface $conn){
        $this->clients->detach($conn);
        echo 'Connection '.$conn->resourceId.' has disconnected'."\n";
    }


    public function onError(ConnectionInterface $conn, \Exception $e){
        echo 'An error has occured: '.$e->getMessage()."\n";
        $conn -> close();
    }
    //Socket actions end
    
    
    protected static function dropCardFromDeck($deck, $card){
        $deck = array_values($deck);
        //Количество карт в входящей колоде
        $deck_card_count = count($deck);
        for($i=0; $i<$deck_card_count; $i++){
            if(!is_array($deck[$i])){
                $deck[$i] = get_object_vars($deck[$i]);
            }
            if(Crypt::decrypt($deck[$i]['id']) == Crypt::decrypt($card->id)){//Если id сходятся
                unset($deck[$i]);//Сносим карту из входящей колоды
                break;
            }
        }
        $deck = array_values($deck);
        return $deck;
    }

    protected static function searchUserInBattle($user_id, $battle_members){
        foreach($battle_members as $key => $value){
            if($user_id == $value->user_id){
                return $value;
            }
        }
    }


    protected static function resetBattleFieldCardsStrength($battle_field){
        foreach($battle_field as $field => $rows){
            if($field != 'mid'){
                foreach($rows as $row => $cards){
                    foreach($cards['warrior'] as $i => $card_data){
                        if(!is_array($card_data['card'])){;
                            $card_array_data = get_object_vars($card_data['card']);
                        }else{
                            $card_array_data = $card_data['card'];
                        }
                        $battle_field[$field][$row]['warrior'][$i]['strength'] = $card_array_data['strength'];
                    }
                }
            }
        }
        return $battle_field;
    }


    protected static function recalculateStrengthByMid($battle_field, $user_array, $opponent_array){
        //Сброс значений силы
        $battle_field = self::resetBattleFieldCardsStrength($battle_field);
        $actions_array_fear = [];//Массив действий "Страшный"
        $actions_array_support = [];//Массив действий "Поддержка"
        $actions_array_fury = [];//Массив действий "Неистовство"
        $actions_array_brotherhood = [];//Массив действий "Боевое братство"
        
        foreach($battle_field as $field => $rows){
            if($field != 'mid'){
                foreach ($rows as $row => $cards){
                    foreach($cards['warrior'] as $i => $card_data){
                        
                        if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars($card_data['card']);
                        
                        foreach($card_data['card']['actions'] as $j => $action){
                            if(!is_array($action)) $action = get_object_vars ($action);
                            if($action['action'] == '16'){
                                $actions_array_brotherhood[$field][Crypt::decrypt($card_data['card']['id'])] = $card_data;
                                break;
                            }
                            
                            if($action['action'] == '17'){
                                $actions_array_support[$field.'_'.$row.'_'.$i] = $card_data;
                            }
                            
                            if($action['action'] == '19'){
                                $actions_array_fury[$field.'_'.$row.'_'.$i] = $card_data;
                            }
                            
                            if($action['action'] == '21'){
                                $actions_array_fear[$field][Crypt::decrypt($card_data['card']['id'])] = $card_data;
                            }
                        }
                    }
                }
            }else{
                foreach($rows as $card_data){
                    if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars($card_data['card']);
                    foreach($card_data['card']['actions']as $j => $action){
                        if(!is_array($action)) $action = get_object_vars ($action);
                        if($action['action'] == '21'){
                            if(!isset($actions_array_fear[Crypt::decrypt($card_data['card']['id'])])){
                                $actions_array_fear['mid'][Crypt::decrypt($card_data['card']['id'])] = $card_data;
                            }
                        }
                    }
                }
            }
        }echo __LINE__."\n";

        //Применение действия "Страшный" к картам
        foreach($actions_array_fear as $source => $cards){
            
            foreach($cards as $card_id => $card_data){
                if(!is_array($card_data)) $card_data = get_object_vars($card_data);
                
                $user_current_deck = ($card_data['login'] == $user_array['player']) ? $user_array['current_deck'] : $opponent_array['current_deck'];
                
                foreach($card_data['card']['actions'] as $action_iter => $action){
                    if($action -> action == '21'){
                        //Карта действует на всех или только на противника
                        if($action->CAfear_actionTeamate == 1){
                            $players = ['p1', 'p2'];
                        }else{
                            if($card_data['login'] == $user_array['login']){
                                $players = [$opponent_array['player']];
                            }else{
                                $players = [$user_array['player']];
                            }
                        }
                        
                        //Карта действует на группу
                        if(isset($action->CAfear_actionToGroupOrAll)){
                            $groups = $action->CAfear_actionToGroupOrAll;
                        }else{
                            $groups = [];
                        }
                        
                        if(in_array($user_current_deck, $action->CAfear_enemyRace)){
                            
                            for($i=0; $i<count($players); $i++){
                                foreach($battle_field[$players[$i]] as $row => $cards_to_fear){
                                    
                                    if(in_array($row, $action->CAfear_ActionRow)){
                                        $allow_fear = true;
                                        //Если в ряду оказаалась карта "Исцеление"
                                        if(!empty($cards_to_fear['special'])){
                                            foreach($cards_to_fear['special']['card']->actions as $card_to_fear_action_iterator => $card_to_fear_action){
                                                if($card_to_fear_action -> action == '25'){
                                                    $allow_fear = false;
                                                }
                                            }
                                        }
                                        
                                        if($allow_fear){
                                            foreach($cards_to_fear['warrior'] as $cards_to_fear_iter => $card_to_fear_data){
                                                //Если у карты есть имммунитет
                                                $immune = false;
                                                foreach($card_to_fear_data['card']['actions'] as $imune_iter => $current_card_action){
                                                    if($current_card_action -> action == '18'){
                                                        $immune = true;
                                                    }
                                                }
                                                
                                                if(($card_to_fear_data['strength'] > 0) && (!$immune)){
                                                    //Если действие приемлемо только к группе
                                                    if(!empty($groups)){
                                                        foreach($card_to_fear_data['card']->groups as $groups_ident => $group_id){
                                                            if(in_array($group_id, $groups)){
                                                                $strength = $card_to_fear_data['strength'] - $action->CAfear_strenghtValue;
                                                                if($strength < 1){
                                                                    $strength = 1;
                                                                }
                                                                $battle_field[$players[$i]][$row]['warrior'][$cards_to_fear_iter]['strength'] = $strength;
                                                            }
                                                        }
                                                    }else{
                                                        $strength = $card_to_fear_data['strength'] - $action->CAfear_strenghtValue;
                                                        if($strength < 1){
                                                            $strength = 1;
                                                        }
                                                        $battle_field[$players[$i]][$row]['warrior'][$cards_to_fear_iter]['strength'] = $strength;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }echo __LINE__."\n";
        
        //Применение "Поддержка" к картам
        foreach($actions_array_support as $card_id => $card_data){
            if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars($card_data['card']);
            
            if($card_data['login'] == $user_array['login']){
                $player = $user_array['player'];
            }else{
                $player = $opponent_array['player'];
            }
            
            $target_rows = [];
            foreach($card_data['card']['actions'] as $i => $actions){
                if($actions->action == '17'){
                    $target_rows = $actions->CAsupport_ActionRow;
                    if($actions->CAsupport_selfCast == 0){
                        $self_cast = false;
                    }else{
                        $self_cast = true;
                    }
                    
                    if( (isset($actions->CAsupport_actionToGroupOrAll)) && ($actions->CAsupport_actionToGroupOrAll != 0)){
                        $groups = $actions->CAsupport_actionToGroupOrAll;
                    }else{
                        $groups = [];
                    }
                }
            }
            
            foreach($target_rows as $row_iter => $row){
                foreach($battle_field[$player][$row]['warrior'] as $i => $card){
                    
                    if(!is_array($card['card'])) $card['card'] = get_object_vars ($card['card']);

                    $allow_support = true;
                    foreach($card['card']['actions'] as $j => $action){
                        if($action->action == '18'){
                            if($action->CAimmumity_type == 1){
                                $allow_support = false;
                            }
                        }
                    }
                    
                    if($allow_support){
                        if(!empty($groups)){
                            foreach($card['card']['groups'] as $groups_ident => $group_id){
                                
                                if(in_array($group_id, $groups)){

                                    if(($card['card']['id'] == $card_data['card']['id']) && ($card_id == $player.'_'.$row.'_'.$i)){
                                        if($self_cast){
                                            $strength = $card['strength'] + $actions->CAsupport_strenghtValue;
                                        }else{
                                            $strength = $card['strength'];
                                            $self_cast = true;
                                        }
                                    }else{
                                        $strength = $card['strength'] + $actions->CAsupport_strenghtValue;
                                    }
                                    $battle_field[$player][$row]['warrior'][$i]['strength'] = $strength;
                                }
                            }
                        }else{
                            if(($card['card']['id'] == $card_data['card']['id']) && ($card_id == $player.'_'.$row.'_'.$i)){
                                if($self_cast){
                                    $strength = $card['strength'] + $actions->CAsupport_strenghtValue;
                                }else{
                                    $strength = $card['strength'];
                                    $self_cast = true;
                                }
                            }else{
                                $strength = $card['strength'] + $actions->CAsupport_strenghtValue;
                            }
                            $battle_field[$player][$row]['warrior'][$i]['strength'] = $strength;
                        }
                    }
                }
            }
        }echo __LINE__."\n";
        
        //Применение "Неистовость" к картам
        foreach($actions_array_fury as $card_id => $card_data){
            if($card_data['login'] == $user_array['login']){
                $players = $opponent_array['player'];
            }else{
                $players = $user_array['player'];
            }
            
            $allow_fury_by_race = false; //Неистовость запрещена
            
            foreach($card_data['card']['actions'] as $action_iter => $action){
                if($action->action == '19'){
                    //Колода противника вызывает у карты неистовство
                    if($card_data['login'] == $user_array['login']){
                        if(in_array($opponent_array['current_deck'], $action->CAfury_enemyRace)){
                            $allow_fury_by_race = true;
                        }
                    }else{
                        if(in_array($user_array['current_deck'], $action->CAfury_enemyRace)){
                            $allow_fury_by_race = true;
                        }
                    }
                    //Количество воинов в ряду/рядах вызывает неистовство
                    if((isset($action->CAfury_ActionRow)) && (!empty($action->CAfury_ActionRow))){
                        $row_cards_count = 0;
                        
                        $fury_rows = $action->CAfury_ActionRow;
                                
                        for($i=0; $i<count($fury_rows); $i++){
                            $row_cards_count += count($battle_field[$players][$fury_rows[$i]]['warrior']);
                        }
                        
                        $allow_fury_by_row = ($row_cards_count >= $action->CAfury_enemyHasSuchNumWarriors) ? true : false;
                        
                    }else{
                        $allow_fury_by_row = true;
                    }
                    
                    if( (isset($action->CAfury_abilityCastEnemy)) && (!empty($action->CAfury_abilityCastEnemy)) ){
                        $allow_fury_by_magic = true;
                    }else{
                        $allow_fury_by_magic = true;
                    }
                    
                    if(($allow_fury_by_row) && ($allow_fury_by_race) && ($allow_fury_by_magic)){
                        $card_destignation = explode('_',$card_id);
                        $battle_field[$card_destignation[0]][$card_destignation[1]]['warrior'][$card_destignation[2]]['strength'] += $action->CAfury_addStrenght;
                    }
                }
            }
        }echo __LINE__."\n";
        
        //Применение "Боевое братство" к картам
        $cards_to_brotherhood = [];
        foreach($actions_array_brotherhood as $player => $cards_array){
            foreach($cards_array as $card_id => $card_data){
                if(!is_array($card_data['card'])) $card_data['card'] = get_object_vars($card_data['card']);
                
                foreach($card_data['card']['actions'] as $action_iter => $action){
                    if(!is_array($action)) $action = get_object_vars($action);
                    
                    if($action['action'] == '16'){
                        if($action['CAbloodBro_actionToGroupOrSame'] == 0){
                            $count_same = 0;
                            $mult_same = 1;
                            foreach($battle_field[$player] as $rows => $cards){
                                foreach($cards['warrior'] as $card_iter => $card){
                                    if(Crypt::decrypt($card_data['card']['id']) == Crypt::decrypt($card['card']['id'])){
                                        $count_same++;
                                    }
                                }
                            }
                            if($count_same > 0){
                                $mult_same = $count_same;
                                if($mult_same > $action['CAbloodBro_strenghtMult']){
                                    $mult_same = $action['CAbloodBro_strenghtMult'];
                                }
                            }
                            foreach($battle_field[$player] as $rows => $cards){
                                foreach($cards['warrior'] as $card_iter => $card){
                                    if(Crypt::decrypt($card_data['card']['id']) == Crypt::decrypt($card['card']['id'])){
                                        $battle_field[$player][$rows]['warrior'][$card_iter]['strength'] *= $mult_same;
                                    }
                                }
                            }

                        }else{
                            foreach($battle_field[$player] as $rows => $cards){
                                foreach($cards['warrior'] as $card_iter => $card){
                                    for($i=0; $i<count($card['card']['groups']); $i++){
                                        if(in_array($card['card']['groups'][$i], $action['CAbloodBro_actionToGroupOrSame'])){
                                            
                                            $cards_to_brotherhood[$player][$card['card']['groups'][$i].'_'.$action['CAbloodBro_strenghtMult']][] = Crypt::decrypt($card['card']['id']);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }echo __LINE__."\n";
        
        if( (isset($cards_to_brotherhood)) && (!empty($cards_to_brotherhood)) ){
            foreach($cards_to_brotherhood as $player => $group_data){
                foreach($group_data as $group_ident => $cards_ids){
                    $cards_to_brotherhood[$player][$group_ident] = array_unique($cards_to_brotherhood[$player][$group_ident]);
                }
            }
            
            foreach($cards_to_brotherhood as $player => $group_data){
                foreach($group_data as $group_ident => $cards_ids){
                    $group_data = explode('_', $group_ident);
                    $count_group = 0;
                    $mult_group = 1;
                    foreach($battle_field[$player] as $row => $cards){
                        foreach($cards['warrior'] as $card_iter => $card){
                            if(in_array(Crypt::decrypt($card['card']['id']), $cards_ids)){
                                $count_group++;
                            }
                        }
                    }
                    if($count_group > 0){
                        $mult_group = $count_group;
                        if($mult_group > $group_data[1]){
                            $mult_group = $group_data[1];
                        }
                    }
                    
                    foreach($battle_field[$player] as $row => $cards){
                        foreach($cards['warrior'] as $card_iter => $card){
                            if(in_array(Crypt::decrypt($card['card']['id']), $cards_ids)){
                                $battle_field[$player][$row]['warrior'][$card_iter]['strength'] *= $mult_group;
                            }
                        }
                    }
                }
            }
        }echo __LINE__."\n";
        
        //Применение Воодушевления
        foreach($battle_field as $player => $rows){
            foreach($rows as $row => $cards){
                if(!empty($cards['special'])){
                    foreach($cards['special']['card']->actions as $action_iter => $action_data){
                        if($action_data->action == '28'){
                                            
                            foreach($battle_field[$player][$row]['warrior'] as $i => $card_data){
                                if($action_data->CAinspiration_modificator == 0){
                                    $battle_field[$player][$row]['warrior'][$i]['strength'] *= $action_data->CAinspiration_multValue;
                                }else{
                                    $battle_field[$player][$row]['warrior'][$i]['strength'] += $action_data->CAinspiration_multValue;
                                }
                            }
                        }
                    }
                }
            }
        }echo __LINE__."\n";
        
        return $battle_field;
    }

    
    protected static function strRowToInt($field){
        switch($field){ //Порядковый номер поля
            case 'meele':       $field_row = 0; break;
            case 'range':       $field_row = 1; break;
            case 'superRange':  $field_row = 2; break;
            case 'sortable-cards-field-more': $field_row = 3; break;
        }
        return $field_row;
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

}