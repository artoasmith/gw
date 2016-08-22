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

        $users = [];
        foreach($battle_members as $key => $value){
            $current_user = \DB::table('users')->select('id','login','user_current_deck')->where('id','=',$value->user_id)->get();
            $user_identificator = ($value->user_id == $battle->creator_id) ? 'p1' : 'p2';
            $users[$value->user_id] = [
                'login'             => $current_user[0]->login,
                'user_current_deck' => $current_user[0]->user_current_deck,
                'user_identificator'=> $user_identificator
            ];
        }

        SiteFunctionsController::updateUserInBattleConnection($msg->ident->userId);//Обновление пользовательского статуса online

        $round_ends = self::checkRoundEnds($battle, $battle_members, $msg, $users);

        if(!$round_ends){
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
                                    $user = $users[$value->user_id];
                                    if($user['user_current_deck'] == 'cursed'){
                                        $cursed_players[] = $value->user_id;
                                    }
                                }

                                if(count($cursed_players) == 1){//Если за столом есть 1н игрок из фракции "Проклятые"
                                    $players_turn = $cursed_players[0];
                                }else{
                                    $players_turn = $battle_members[rand(0,$ready_players_count-1)] -> user_id;
                                }

                                $user = $users[$players_turn];

                            }else{
                                $user = $users[$battle->user_id_turn];
                                $players_turn = $battle->user_id_turn;
                            }

                            $result = ['message' => 'allUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user['login']];

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
                                $user_turn = $users[$battle -> user_id_turn];
                            }else{
                                $user_turn['login'] = '';
                            }

                            $user = $users[$msg->ident->userId]; // Данные пользователя

                            $result = ['message' => 'usersAreJoined', 'JoinedUser' => $user['login'], 'login' => $user_turn['login'], 'battleInfo' => $msg->ident->battleId];

                            self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                            self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                        }
                    }

                    if($battle -> fight_status == 2){

                        if($battle -> user_id_turn != 0){
                            $user_turn = $users[$battle -> user_id_turn];
                        }else{
                            $user_turn['login'] = '';
                        }
                        $result = ['message' => 'allUsersAreReady', 'battleInfo' => $msg->ident->battleId, 'login' => $user_turn['login']];
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
                                    if($card_data['login'] == $users[$msg->ident->userId]['login']){
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
                        foreach($users as $user_id => $user_data){
                            if($user_id == $msg->ident->userId){
                                $current_user_battle_data = self::searchUserInBattle($msg->ident->userId, $battle_members);
                                $user_array = [
                                    'login'         => $user_data['login'],
                                    'user_id'       => $user_id,
                                    'user_identificator'=> $user_data['user_identificator'],
                                    'user_deck'     => unserialize($current_user_battle_data->user_deck),
                                    'user_hand'     => unserialize($current_user_battle_data->user_hand),
                                    'user_discard'  => unserialize($current_user_battle_data->user_discard),
                                    'round_passed'  => $current_user_battle_data->round_passed,
                                    'battle_id'     => $current_user_battle_data->id,
                                    'user_current_deck'=> $user_data['user_current_deck']
                                ];
                            }else{
                                $current_user_battle_data = self::searchUserInBattle($user_id, $battle_members);
                                $oponent_array = [
                                    'login'         => $user_data['login'],
                                    'user_id'       => $user_id,
                                    'user_identificator'=> $user_data['user_identificator'],
                                    'user_deck'     => unserialize($current_user_battle_data->user_deck),
                                    'user_hand'     => unserialize($current_user_battle_data->user_hand),
                                    'user_discard'  => unserialize($current_user_battle_data->user_discard),
                                    'round_passed'  => $current_user_battle_data->round_passed,
                                    'battle_id'     => $current_user_battle_data->id,
                                    'user_current_deck'=> $user_data['user_current_deck']
                                ];
                            }
                        }

                        //Если пользователь не спасовал
                        if($user_array['round_passed'] == 0){
                            $card = json_decode(SiteGameController::getCardData($msg->card));//Получаем данные о карте
                            
                            $field_row = self::strRowToInt($msg->field);
                            
                            if((in_array($field_row, $card->action_row, true)) || ($msg->field == 'sortable-cards-field-more')){ //Если номер поля не подделал пользователь

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
                                    if(($action->action == '12')||(($action->action == '13')&&($card->type == 'special'))||($action->action == '26')){ // Если карта кидается на поле противника
                                        if($user_battle_field_identificator == 'p1'){
                                            $user_battle_field_identificator = 'p2';
                                            $oponent_battle_field_identificator = 'p1';
                                        }else{
                                            $user_battle_field_identificator = 'p1';
                                            $oponent_battle_field_identificator = 'p2';
                                        }
                                    }
                                }

                                $battle_field = unserialize($battle->battle_field);//Данные о поле битвы

                                if($card->type == 'special'){//Если карта относится к специальным
                                    
                                    if($user_battle_field_identificator == 'mid'){//Если карта выкинута на поле спец карт
                                        if(count($battle_field['mid']) > 6){//Если карт на поле спец карт больше 6ти
                                            //Кидает первую карту в отбой
                                            if($user_array['login'] == $battle_field['mid'][0]['login']){
                                                $user_array['user_discard'][] = $battle_field['mid'][0];
                                            }else{
                                                $oponent_array['user_discard'][] = $battle_field['mid'][0];
                                            }
                                            unset($battle_field['mid'][0]); //Удаляем первую карту
                                        }

                                        $battle_field['mid'][] = ['card' => $card, 'strength' => $card->strength, 'login' => $user_array['login']]; //Добавляем текущую карту на поле боя и её принадлежность пользователю
                                        $battle_field['mid'] = array_values($battle_field['mid']);"234\n";
                                    }else{
                                        foreach($card->actions as $i => $action){
                                            //Если логика карт предусматривает сразу уходить в отбой
                                            if( (($action->action == '13')&&($card->type == 'special'))||($action->action == '24')||($action->action == '27')||($action->action == '29')){
                                                $user_array['user_discard'][] = $card->id;
                                            }else{
                                                $battle_field[$user_battle_field_identificator][$field_row]['special'] = ['card' => $card, 'strength' => $card->strength, 'login' => $user_array['login']];
                                            }
                                        }
                                    }
                                //Если карта относится к картам воинов 
                                }else{
                                    $battle_field[$user_battle_field_identificator][$field_row]['warrior'][] = ['card'=>$card, 'strength'=>$card->strength, 'login' => $user_array['login']];
                                }

                                /*Перебор действий карты*/
                                $new_cards = []; //Добавочные карты в руке

                                foreach($card->actions as $action_iter => $action_data){
                                    //ШПИОН
                                    if($action_data->action == '12'){
                                        $deck_card_count = count($user_array['user_deck']);echo "270\n";

                                        $n = ($deck_card_count >= $action_data->CAspy_get_cards_num) ? $action_data->CAspy_get_cards_num : $deck_card_count;
                                        for($i=0; $i<$n; $i++){echo "273\n";

                                            $rand_item = rand(0, $deck_card_count-1);echo "275\n";
                                            $random_card = $user_array['user_deck'][$rand_item];
                                            $user_array['user_hand'][] = $random_card;
                                            $new_cards[] = $random_card;

                                            unset($user_array['user_deck'][$rand_item]);

                                            $user_array['user_deck'] = array_values($user_array['user_deck']);
                                            $deck_card_count = count($user_array['user_deck']);
                                        }
                                    }
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

                                                foreach($users as $user_id => $user_data){
                                                    if($user_id != $msg->ident->userId){
                                                        $enemy_player = $user_data['user_identificator'];
                                                    }
                                                }

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
                                                            $rows_strength += $card_data['strength'];//Сумарная сила выбраных рядов

                                                            $can_kill_this_card = 1; //Имунитет к убийству 1 - не имеет иммунитет; 0 - не имеет
                                                            foreach($card_data['card']->actions as $j => $action_immune){
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

                                                            if($card_data['strength'] < $action_data->CAkiller_enemyStrenghtLimitToKill){
                                                                //Игнор к иммунитету
                                                                $allow_to_kill_by_immune = 1; //Разрешено убивать карту т.к. иммунитет присутствует

                                                                foreach($card_data['card']->actions as $card_action_i => $card_current_action){
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
                                                                        foreach($card_data['card']->groups as $groups_ident => $group_id){
                                                                            if(in_array($group_id, $groups)){
                                                                                $cards_can_be_destroyed[] = ['player' => $fields, 'card_id' => $card_data['card']->id];
                                                                            }
                                                                        }
                                                                    }else{
                                                                        $cards_can_be_destroyed[] = ['player' => $fields, 'card_id' => $card_data['card']->id];
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
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
                                                    if($card_data['card']->id == $cards_to_destroy[$i]['card_id']){

                                                        if($user_array['user_identificator'] == $cards_to_destroy[$i]['player']){
                                                            $user_array['user_discard'][] = $card_data['card']->id;
                                                        }else{
                                                            $oponent_array['user_discard'][] = $card_data['card']->id;
                                                        }
                                                        unset($battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior'][$card_iterator]);
                                                        $battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior'] = array_values($battle_field[$cards_to_destroy[$i]['player']][$rows]['warrior']);

                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //END OF УБИЙЦА

                                    //РАЗВЕДЧИК
                                    if($action_data->action == '15'){
                                        $deck_card_count = count($user_array['user_deck']);
                                        if($deck_card_count > 0){
                                            $rand_item = rand(0, $deck_card_count-1);
                                            $random_card = $user_array['user_deck'][$rand_item];
                                            $user_array['user_hand'][] = $random_card;
                                            $new_cards[] = $random_card;
                                            unset($user_array['user_deck'][$rand_item]);

                                            $user_array['user_deck'] = array_values($user_array['user_deck']);
                                        }
                                    }
                                    //END OF РАЗВЕДЧИК

                                    //CТРАШНЫЙ
                                    if($action_data->action == '21'){
                                        $players = ($action_data->CAfear_actionTeamate == 1) ? ['p1', 'p2'] : [$oponent_array['user_identificator']]; //Карта действует на всех или только на противника
                                        foreach($players as $field){
                                            foreach($battle_field[$field] as $row => $cards){//перебираем карты в рядах
                                                if(in_array($row, $action_data->CAfear_ActionRow)){//Если данный ряд присутствует в области действия карты "Страшный"
                                                    if(!empty($cards['special'])){
                                                        //Проверяем присутсвует ли карта "Исцеление" в текущем ряду
                                                        foreach($cards['special']['card']->actions as $i => $action){
                                                            if($action->action == '25'){
                                                                if($user_array['user_identificator'] == $field){//Кидаем карту "Исцеление" в отбой
                                                                    $user_array['user_discard'][] = $cards['special']['card']->id;
                                                                }else{
                                                                    $oponent_array['user_discard'][] = $cards['special']['card']->id;
                                                                }
                                                                $battle_field[$field][$row]['special'] = '';
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //END OF CТРАШНЫЙ

                                    //ИСЦЕЛЕНИЕ
                                    if($action_data->action == '25'){
                                        foreach($battle_field['mid'] as $i => $card_data){
                                            foreach($card_data['card']->actions as $action_iterrator => $action){
                                                if($action->action == '21'){
                                                    if(in_array($field_row, $action->CAfear_ActionRow)){
                                                        if($user_array['login'] == $card_data['login']){
                                                            $user_array['user_discard'][] = $card_data['card']->id;
                                                        }else{
                                                            $oponent_array['user_discard'][] = $card_data['card']->id;
                                                        }
                                                        unset($battle_field['mid'][$i]);
                                                        $battle_field['mid'] = array_values($battle_field['mid']);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    //END OF ИСЦЕЛЕНИЕ
                                    
                                    //ОДУРМАНИВАНИЕ
                                    if($action_data->action == '23'){
                                        $cards_can_be_obscured = [];
                                        $min_strength = 999;
                                        $max_strength = 0;
                                        echo "506\n";
                                        foreach($battle_field[$oponent_array['user_identificator']] as $row => $cards){echo "507\n";
                                            if(in_array($row, $action_data->CAobscure_ActionRow)){echo "508\n";
                                                foreach($cards['warrior'] as $i => $card_data){echo "509\n";
                                                    if($card_data['strength'] <= $action_data->CAobscure_maxCardStrong){echo "510\n";
                                                        $max_strength = ($card_data['strength'] > $max_strength) ? $card_data['strength'] : $max_strength;
                                                        $min_strength = ($card_data['strength'] < $min_strength) ? $card_data['strength'] : $max_strength;
                                                        $cards_can_be_obscured[] = ['card'=>$card_data['card'], 'strength' => $card_data['strength'], 'row'=>$row];
                                                    }
                                                } 
                                            }
                                        }echo "517\n";
                                        
                                        if(!empty($cards_can_be_obscured)){
                                            switch($action_data->CAobscure_strenghtOfCardToObscure){
                                                case '0': $card_strength_to_obscure = $min_strenght; break;//Самую слабую
                                                case '1': $card_strength_to_obscure = $max_strenght; break;//Самую сильную
                                                case '2':
                                                    $random = rand(0, count($cards_can_be_obscured)-1);
                                                    $card_strength_to_obscure = $cards_can_be_obscured[$random]['strength'];
                                                    break;
                                            } echo "526\n";
                                        }
                                        
                                        $cards_to_obscure = [];
                                        if(!empty($cards_can_be_obscured)){
                                            for($i=0; $i<$action_data->CAobscure_quantityOfCardToObscure; $i++){ echo "528\n";
                                                for($j=0; $j<count($cards_can_be_obscured); $j++){ echo "529\n";
                                                    if($card_strength_to_obscure == $cards_can_be_obscured[$j]['strength']){ echo "530\n";
                                                        $cards_to_obscure[] = $cards_can_be_obscured[$j];
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        for($i=0; $i<count($cards_to_obscure); $i++){ echo "536\n";
                                            foreach($battle_field[$oponent_array['user_identificator']][$cards_to_obscure[$i]['row']]['warrior'] as $j => $card_data){ echo "537\n";
                                                if(Crypt::decrypt($cards_to_obscure[$i]['card']->id) == Crypt::decrypt($card_data['card']->id)){ echo "538\n";
                                                    $battle_field[$user_array['user_identificator']][$cards_to_obscure[$i]['row']]['warrior'][] = $card_data;
                                                    unset($battle_field[$oponent_array['user_identificator']][$cards_to_obscure[$i]['row']]['warrior'][$j]); echo "540\n";
                                                    $battle_field[$oponent_array['user_identificator']][$cards_to_obscure[$i]['row']]['warrior'] = array_values($battle_field[$oponent_array['user_identificator']][$cards_to_obscure[$i]['row']]['warrior']);
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    //END OF ОДУРМАНИВАНИЕ
                                    
                                    //ПЕРЕГРУППИРОВКА
                                    if($action_data->action == '24'){
                                        foreach($battle_field[$msg->player][$field_row]['warrior'] as $i => $card_data){
                                            if($card_data['card']->id == $msg->retrieve){
                                                unset($battle_field[$msg->player][$field_row]['warrior'][$i]);
                                                $battle_field[$msg->player][$field_row]['warrior'] = array_values($battle_field[$msg->player][$field_row]['warrior']);
                                                $user_array['user_hand'][] = get_object_vars($card_data['card']);
                                                $new_cards[] = get_object_vars($card_data['card']);
                                            }
                                        }
                                    }
                                    //END OF ПЕРЕГРУППИРОВКА
                                }
                                /*END OF Перебор действий карты*/

                                $battle_field = self::recalculateStrengthByMid($battle_field, $user_array, $oponent_array);
                                
                                $user_hand_card_count = count($user_array['user_hand']); //Количество карт в колоде
                                for($i=0; $i<$user_hand_card_count; $i++){
                                    if(Crypt::decrypt($user_array['user_hand'][$i]['id']) == Crypt::decrypt($card->id)){//Если id сходятся
                                        unset($user_array['user_hand'][$i]);//Сносим карту с руки
                                        break;
                                    }
                                }

                                if(count($user_array['user_hand']) == 0){//Если у пользлвателя закончились карты на руках - делаем ему автопас
                                    \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_id'])->update(['round_passed' => '1']);
                                }

                                $user_discard_count = count($user_array['user_discard']);
                                $user_deck_count = count($user_array['user_deck']);
                                $user_hand_count = count($user_array['user_hand']);

                                $oponent_discard_count = count($oponent_array['user_discard']);
                                $oponent_deck_count = count($oponent_array['user_deck']);

                                $user_array['user_deck'] = serialize(array_values($user_array['user_deck']));
                                $user_array['user_hand'] = serialize(array_values($user_array['user_hand']));
                                $user_array['user_discard'] = serialize(array_values($user_array['user_discard']));

                                $oponent_array['user_deck'] = serialize(array_values($oponent_array['user_deck']));
                                $oponent_array['user_hand'] = serialize(array_values($oponent_array['user_hand']));
                                $oponent_array['user_discard'] = serialize(array_values($oponent_array['user_discard']));
                                //Сохраняем руку, колоду и отбой опльзователя
                                \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_id'])->update(['user_deck'=>$user_array['user_deck'], 'user_hand' => $user_array['user_hand'], 'user_discard' => $user_array['user_discard']]);
                                \DB::table('tbl_battle_members')->where('id', '=', $oponent_array['battle_id'])->update(['user_deck'=>$oponent_array['user_deck'], 'user_hand' => $oponent_array['user_hand'], 'user_discard' => $oponent_array['user_discard']]);
                                //Сохраняем поле битвы
                                $battle->battle_field = serialize($battle_field);
                                $battle->save();

                                if($user_hand_count == 0){
                                    \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_id'])->update(['round_passed' => '1']);
                                }

                                /*
                                 * Выход:
                                 * message = userMadeAction -> Пользователь сделал действие
                                 * field_data -> карты на поле
                                 * user_hand -> карты руки пользователя
                                 * counts [user_discard_count, opon_discard_count, opon_deck_count]
                                 */

                                $user_turn = self::changeUserTurn($msg->ident->battleId);
                                $user = $users[$user_turn];

                                $user_array['user_discard'] = unserialize($user_array['user_discard']);
                                $user_discard = [];
                                for($i=0; $i<$user_discard_count; $i++){
                                    $user_discard[] = SiteGameController::getCardData($user_array[$i]);
                                }
                                
                                $result = [
                                    'message'   => 'userMadeAction',
                                    'field_data'=> $battle_field,
                                    'new_cards' => $new_cards,
                                    'user_deck' => unserialize($user_array['user_deck']),
                                    'user_discard' => $user_discard,
                                    'counts'    => [
                                        'user_deck'    => $user_deck_count,
                                        'user_discard' => $user_discard_count,
                                        'opon_discard' => $oponent_discard_count,
                                        'opon_deck'    => $oponent_deck_count
                                    ],
                                    'battleInfo'=> $msg->ident->battleId,
                                    'login' => $user['login']
                                ];

                                self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                                $result = [
                                    'message'   => 'userMadeAction',
                                    'field_data'=> $battle_field,
                                    'counts'    => [
                                        'user_deck'    => $oponent_deck_count,
                                        'user_discard' => $oponent_discard_count,
                                        'opon_discard' => $user_discard_count,
                                        'opon_deck'    => $user_deck_count
                                    ],
                                    'battleInfo'=> $msg->ident->battleId,
                                    'login' => $user['login']
                                ];
                                self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                            }
                        }else{
                            $user = $users[$msg->ident->userId];
                            $result = ['message' => 'userMadeAction', 'field_data' => unserialize($battle->battle_field), 'battleInfo' => $msg->ident->battleId, 'login' => $user['login']];
                            //Узнаем количество пасанувших пользователей
                            $round_ends = self::checkRoundEnds($battle, $battle_members, $msg, $users);

                            //Если хотябы один пользователь не спасовал
                            if($round_ends){
                                self::sendMessageToSelf($from, $round_ends); //Отправляем результат отправителю
                                self::sendMessageToOthers($from, $round_ends, $this->battles[$msg->ident->battleId]);
                            }else{
                                self::sendMessageToSelf($from, $result);
                                self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                            }
                        }
                    }
                    break;
            }

        }else{
            self::sendMessageToSelf($from, $round_ends); //Отправляем результат отправителю
            self::sendMessageToOthers($from, $round_ends, $this->battles[$msg->ident->battleId]);
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
    
    protected static function checkRoundEnds($battle, $battle_members, $msg, $users){
        $users_passed = 0;
        foreach($battle_members as $key => $value){
            if($value -> round_passed > 0) $users_passed++;
        }
        if($users_passed > 1){
            $battle_field = unserialize($battle->battle_field);           
            $result = ['message' => 'roundEnds', 'battleInfo' => $msg->ident->battleId];
            return $result;
        }else{
            return false;
        }
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
                        $battle_field[$field][$row]['warrior'][$i]['strength'] = $card_data['card']->strength;
                    }
                }
            }
        }
        return $battle_field;
    }


    protected static function recalculateStrengthByMid($battle_field, $user_array, $oponent_array){
        $actions_array = []; //Массив действий
        foreach($battle_field as $field => $rows){
            if($field != 'mid'){
                foreach ($rows as $row => $cards){
                    foreach($cards['warrior'] as $i => $card_data){                        
                        foreach($card_data['card']->actions as $j => $action){
                            if($action->action == '21'){
                                $actions_array[Crypt::decrypt($card_data['card']->id)] = [$card_data['card']];
                            }
                        }
                    }
                }
            }else{
                foreach($rows as $card_data){
                    if(!isset($actions_array[Crypt::decrypt($card_data['card']->id)])){
                        $actions_array[Crypt::decrypt($card_data['card']->id)] = $card_data;
                    }
                }
            }
        }

        $battle_field = self::resetBattleFieldCardsStrength($battle_field);

        foreach($actions_array as $card_id => $card_data){
            foreach($card_data['card']->actions as $i => $action){
                
                $players = ($action->CAfear_actionTeamate == 1) ? ['p1', 'p2'] : [$oponent_array['user_identificator']]; //Карта действует на всех или только на противника
                foreach($players as $field){
                    $user_current_deck = ($user_array['user_identificator'] == $field) ? $user_current_deck = $user_array['user_current_deck'] : $user_current_deck = $oponent_array['user_current_deck'];

                    if(in_array($user_current_deck, $action->CAfear_enemyRace)){
                        foreach($battle_field[$field] as $row => $cards){
                            if(in_array($row, $action->CAfear_ActionRow)){
                                $allow_fear = 1;
                                if(!empty($cards['special'])){
                                    foreach($cards['special']->actions as $j => $current_card_action){
                                         if($current_card_action -> action == '25'){
                                             $allow_fear =0;
                                         }
                                    }
                                }
                                if($allow_fear == 1){
                                    foreach($cards['warrior'] as $card_iter => $card_data){
                                        ///!!!ПРОВЕРКА НА ВХОЖДЕНИЕ В ГРУППУ
                                        $immune = 0;
                                        foreach($card_data['card']->actions as $j => $current_card_action){
                                            if($current_card_action -> action == '18'){
                                                $immune = 1;
                                            }
                                        }
                                        if(($card_data['strength'] > 0) && ($immune == 0)){
                                            $strength = $card_data['strength'] - $action->CAfear_strenghtValue;
                                            if($strength < 1){
                                                $strength = 1;
                                            }
                                            $battle_field[$field][$row]['warrior'][$card_iter]['strength'] = $strength;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $battle_field;
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