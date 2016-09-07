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
    //Обработчик каждого сообщения
    public function onMessage(ConnectionInterface $from, $msg){
        $msg = json_decode($msg); // сообщение от пользователя arr[action, ident[battleId, UserId, Hash]]
        var_dump($msg);

        if(!isset($this->battles[$msg->ident->battleId])){
            $this->battles[$msg->ident->battleId] = new \SplObjectStorage;
        }

        if(!$this->battles[$msg->ident->battleId]->contains($from)){
            $this->battles[$msg->ident->battleId]->attach($from);
        }
        $SplBattleObj = $this->battles;

        $battle = BattleModel::find($msg->ident->battleId); //Даные битвы

        $battle_members = BattleMembersModel::where('battle_id', '=', $msg->ident->battleId)->get(); //Данные о участвующих в битве

        //Создание массивов пользовательских данных
        foreach($battle_members as $key => $value){
            $current_user = \DB::table('users')->select('id','login','user_current_deck')->where('id','=',$value->user_id)->get();
            $current_user_data = \DB::table('tbl_user_data')->select('user_id','user_energy')->where('user_id','=',$value->user_id)->get();
            
            $user_identificator = ($value->user_id == $battle->creator_id) ? 'p1' : 'p2';

            if($value->user_id == $msg->ident->userId){
                $user_array = [
                    'id'            => $value->user_id,
                    'login'         => $current_user[0]->login,
                    'player'        => $user_identificator,                     //Идентификатор поля пользователя
                    'magic_effects' => unserialize($value->magic_effects),      //Список активных маг. эффектов
                    'energy'        => $current_user_data[0]->user_energy,      //Колличество энергии пользователя
                    'user_deck'     => unserialize($value->user_deck),          //Колода пользователя
                    'user_hand'     => unserialize($value->user_hand),          //Рука пользователя
                    'user_discard'  => unserialize($value->user_discard),       //Отбой пользователя
                    'current_deck'  => $current_user[0]->user_current_deck,     //Название фракции текущей колоды пользоватля
                    'card_source'   => $value->card_source,                     //Источник карт (рука/колода/отбой) текущего хода
                    'card_to_play'  => unserialize($value->card_to_play),       //Массив определенных условиями действия карт при отыгрыше из колоды или отбое
                    'round_passed'  => $value->round_passed,                    //Маркер паса
                    'battle_member_id' => $value->id                            //ID текущей битвы
                ];
            }else{
                $opponent_array = [
                    'id'            => $value->user_id,
                    'login'         => $current_user[0]->login,
                    'player'        => $user_identificator,
                    'magic_effects' => unserialize($value->magic_effects),
                    'energy'        => $current_user_data[0]->user_energy,
                    'user_deck'     => unserialize($value->user_deck),
                    'user_hand'     => unserialize($value->user_hand),
                    'user_discard'  => unserialize($value->user_discard),
                    'current_deck'  => $current_user[0]->user_current_deck,
                    'card_source'   => $value->card_source,
                    'card_to_play'  => unserialize($value->card_to_play),
                    'round_passed'  => $value->round_passed,
                    'battle_member_id' => $value->id
                ];
            }
        }

        SiteFunctionsController::updateUserInBattleConnection($msg->ident->userId);//Обновление пользовательского статуса online

        switch($msg->action){
            //Пользователь готов
            case 'userReady':
                if($battle -> fight_status == 1){//Если пользователи присоединились но карты не выбраны

                    $ready_players_count = 0; //Количество игроков за столом готовых к игре
                    foreach ($battle_members as $key => $value){
                        if($value -> user_ready != 0){
                            $ready_players_count++;
                        }
                    }

                    if($ready_players_count == 2){ //Если готовых к игре
                        if($battle -> user_id_turn == 0){ //Если игрок для хода не определен
                            //Игроки фракции "Проклятые"
                            $cursed_players = [];
                            if($user_array['current_deck'] == 'cursed') $cursed_players[] = ['id'=>$user_array['id'], 'login'=> $user_array['login']];
                            if($opponent_array['current_deck'] == 'cursed') $cursed_players[] = ['id'=>$opponent_array['id'], 'login'=> $opponent_array['login']];
                            //Если за столом есть 1н игрок из фракции "Проклятые"
                            if(count($cursed_players) == 1){
                                $players_turn = $cursed_players[0]['id'];
                                $user = $cursed_players[0]['login'];
                            }else{//Если оба игрока в фракции "Проклятые" или оба других фракций
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

                        $result = [
                            'message'   => 'allUsersAreReady',
                            'cardSource'=> $opponent_array['card_source'],
                            'cardToPlay'=> $opponent_array['card_to_play'],
                            'battleInfo'=> $msg->ident->battleId,
                            'login'     => $user
                        ];

                        if($battle -> fight_status <= 1){
                            self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                        }

                        $battle -> fight_status = 2;
                        if($battle -> user_id_turn == 0){
                            $battle -> user_id_turn = $players_turn;
                            $result = [
                                'message'   => 'allUsersAreReady',
                                'cardSource'=> $user_array['card_source'],
                                'cardToPlay'=> $user_array['card_to_play'],
                                'battleInfo'=> $msg->ident->battleId,
                                'login'     => $user
                            ];
                            self::sendMessageToSelf($from, $result);
                        }
                        $battle -> save();
                    }
                }
                break;


            //Пользователь присоединился
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

                        $result = [
                            'message'   => 'usersAreJoined',
                            'JoinedUser'=> $user_array['login'],
                            'login'     => $user_turn,
                            'battleInfo'=> $msg->ident->battleId
                        ];

                        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    }
                }

                if($battle -> fight_status == 2){
                    $result = [
                        'message'   => 'allUsersAreReady',
                        'cardSource'=> $user_array['card_source'],
                        'cardToPlay'=> $user_array['card_to_play'],
                        'battleInfo'=> $msg->ident->battleId,
                        'login'     => $user_turn
                    ];
                    self::sendMessageToSelf($from, $result);
                }
                break;

            //Вернуть список пользовательских карт
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


            //Пользователь спасовал
            case 'userPassed':
                echo date('Y-m-d H:i:s')."\n";
                $users_passed_count = self::userPassed($msg->ident->userId, $msg->ident->battleId);
                $user_array['round_passed'] = 0;

                $battle_field = unserialize($battle->battle_field);
                
                //Использованые Маг. Эффекты
                $magic_usage = unserialize($battle->magic_usage);
                
                //Если только один пасанувший
                if($users_passed_count == 1){
                    $user_turn = $opponent_array['login'];
                    $user_turn_id = $opponent_array['id'];
                    
                    $battle->user_id_turn = $user_turn_id;
                    $battle->save();

                    self::sendUserMadeActionData($msg, $user_array, $opponent_array, $battle_field, $magic_usage, 'hand', [], $user_turn, $from, $SplBattleObj);
                }

                //Если оба спасовали
                if($users_passed_count == 2){
                    $battle_field = self::recalculateStrengthByMid($battle_field, $user_array, $opponent_array);//Финальный пересчет поля битвы
                    //Подсчет результатп раунда по очкам
                    $total_str = ['p1'=> 0, 'p2'=> 0];
                    foreach($battle_field as $player => $rows){
                        if($player != 'mid'){
                            foreach($rows as $row => $cards){
                                foreach($cards['warrior'] as $card_iter => $card_data){
                                    $total_str[$player] += $card_data['strength'];
                                }
                            }
                        }
                    }
                    //Статус битвы (очки раундов)
                    $round_status = unserialize($battle->round_status);
                    //Результаты раунда отдельно по игрокам
                    $user_points = $total_str[$user_array['player']];
                    $opponent_points = $total_str[$opponent_array['player']];
                    
                    //Определение выигравшего
                    if($user_points > $opponent_points){
                        $round_status[$user_array['player']][] = 1;
                        $round_result = 'Выграл '.$user_array['login'];
                    }
                    if($user_points < $opponent_points){
                        $round_status[$opponent_array['player']][] = 1;
                        $round_result = 'Выграл '.$opponent_array['login'];
                    }
                    if($user_points == $opponent_points){
                        //Если колода пользователя - нечисть и противник не играет нечистью
                        if( ( ($user_array['current_deck'] == 'undead') || ($opponent_array['current_deck'] == 'undead') ) && ($user_array['current_deck'] != $opponent_array['current_deck']) ){
                            if($user_array['current_deck'] == 'undead'){
                                $round_status[$user_array['player']][] = 1;
                                $round_result = 'Выграл '.$user_array['login'];
                            }else{
                                $round_status[$opponent_array['player']][] = 1;
                                $round_result = 'Выграл '.$opponent_array['login'];
                            }
                        }else{
                            $round_status[$user_array['player']][] = 1;
                            $round_status[$opponent_array['player']][] = 1;
                            $round_result = 'Ничья';
                        }
                    }
                    //Отпарвка результатов пользователям
                    $result = ['message'=> 'roundEnds', 'battleInfo' => $msg->ident->battleId, 'roundResult'=>$round_result];
                    self::sendMessageToSelf($from, $result);
                    self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                    
                    $user_turn = $opponent_array['login'];
                    $user_turn_id = $opponent_array['id'];
                    
                    $undead_cards = unserialize($battle->undead_cards);

                    $user_can_left_card = false;
                    
                    //Добавление по карте из колоды каждому игроку
                    $user_array = self::userGainCards($user_array);
                    $opponent_array = self::userGainCards($opponent_array);
                    
                    //Очищение поля битвы от карт
                    foreach($battle_field as $player => $rows){
                        if($player != 'mid'){
                            //Просчет рассовой способности монстров
                            if($user_array['player'] == $player){
                                if($user_array['current_deck'] == 'monsters'){
                                    $user_can_left_card = true;
                                    $card_to_left = self::cardsToLeft($battle_field, $player);
                                }
                            }else{
                                if($opponent_array['current_deck'] == 'monsters'){
                                    $user_can_left_card = true;
                                    $card_to_left = self::cardsToLeft($battle_field, $player);
                                }
                            }

                            foreach($rows as $row => $cards){
                                if($battle_field[$player][$row]['special'] != ''){
                                    if($card_data['login'] == $user_array['login']){
                                        $user_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$row]['special']['card']);
                                    }else{
                                        $opponent_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$row]['special']['card']);
                                    }
                                }
                                $battle_field[$player][$row]['special'] = '';

                                //Заносим карты воинов в отбой
                                if(!empty($cards['warrior'])){
                                    foreach($cards['warrior'] as $card_iter => $card_data){
                                        //Узнаем бессмертная ли текущая карта
                                        $card_is_undead = false;
                                        foreach($card_data['card']['actions'] as $action_iter => $action){
                                            if($action->action == '20'){
                                                $card_is_undead = true;
                                            }
                                        }
                                        //Если у карты есть действие "Бессмертный"
                                        if($card_is_undead){
                                            //Если "Бессмертная" карта участвовала в прошлом раунде
                                            if(in_array(Crypt::decrypt($card_data['card']['id']), $undead_cards[$player]) && ($undead_cards[$player][Crypt::dercrypt($card_data['card']['id'])] > 0) ){
                                                if($player == $user_array['player']){
                                                    $user_array['user_discard'][] = $battle_field[$player][$row]['warrior'][$card_iter]['card'];
                                                }else{
                                                    $opponent_array['user_discard'][] = $battle_field[$player][$row]['warrior'][$card_iter]['card'];
                                                }

                                                unset($battle_field[$player][$row]['warrior'][$card_iter]);
                                                $undead_cards[$player][Crypt::decrypt($card_data['card']['id'])]--;
                                            }else{
                                                if(!isset($undead_cards[$player][Crypt::decrypt($card_data['card']['id'])])){
                                                    $undead_cards[$player][Crypt::decrypt($card_data['card']['id'])] = 1;
                                                }else{
                                                    $undead_cards[$player][Crypt::decrypt($card_data['card']['id'])]++;
                                                }
                                            }

                                        }else{
                                            $allow_to_discard = true;
                                            //если разрешено оставить карту после окончания раунда
                                            if( (isset($card_to_left)) && ($user_can_left_card) ){
                                                foreach($card_to_left as $key => $value){
                                                    $destignation = explode('_',$key);
                                                    if(($player == $destignation[0]) && ($row == $destignation[1]) && ($card_iter == $destignation[2])){
                                                        $allow_to_discard = false;
                                                        $user_can_left_card = false;
                                                    }
                                                }
                                            }

                                            //Заносим карты воинов в отбой
                                            if($allow_to_discard){
                                                if($card_data['login'] == $user_array['login']){
                                                    $user_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$row]['warrior'][$card_iter]['card']);
                                                }else{
                                                    $opponent_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$row]['warrior'][$card_iter]['card']);
                                                }
                                                unset($battle_field[$player][$row]['warrior'][$card_iter]);
                                            }
                                        }
                                    }

                                    $battle_field[$player][$row]['warrior'] = array_values($battle_field[$player][$row]['warrior']);
                                }
                                
                            }
                        }else{
                            foreach($battle_field[$player] as $card_iter => $card_data){
                                if($card_data['login'] == $user_array['login']){
                                    $user_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$card_iter]['card']);
                                }else{
                                    $opponent_array['user_discard'][] = self::transformObjToArr($battle_field[$player][$card_iter]['card']);
                                }
                            }
                        }
                    }
                    $battle_field['mid'] = [];
                    $battle_field = self::recalculateStrengthByMid($battle_field, $user_array, $opponent_array);//Финальный пересчет поля битвы
                    
                    
                    $battle->round_count  = $battle->round_count +1;
                    $battle->round_status = serialize($round_status);
                    $battle->user_id_turn = $user_turn_id;
                    $battle->battle_field = serialize($battle_field);
                    $battle->save();
                    
                    $battle_status = unserialize($battle->round_status);

                    if( (count($battle_status['p1']) == 2) || (count($battle_status['p2']) == 2) ){
                        $battle->fight_status = 3;
                        $battle->save();
                        
                        if( count($battle_status['p1']) > count($battle_status['p2']) ){
                            if($user_array['player'] == 'p1'){
                                $game_result = 'Игру выграл '.$user_array['login'];
                            }else{
                                $game_result = 'Игру выграл '.$opponent_array['login'];
                            }
                        }
                        
                        if( count($battle_status['p1']) < count($battle_status['p2']) ){
                            if($user_array['player'] == 'p2'){
                                $game_result = 'Игру выграл '.$user_array['login'];
                            }else{
                                $game_result = 'Игру выграл '.$opponent_array['login'];
                            }
                        }
                        
                        if( count($battle_status['p1']) == count($battle_status['p2']) ){
                            if( ( ($user_array['current_deck'] == 'undead') || ($opponent_array['current_deck'] == 'undead') ) && ($user_array['current_deck'] != $opponent_array['current_deck']) ){
                                if($user_array['current_deck'] == 'undead'){
                                    $game_result = 'Игру выграл '.$user_array['login'];
                                }else{
                                    $game_result = 'Игру выграл '.$opponent_array['login'];
                                }
                            }else{
                                $game_result = 'Игра сыграна в ничью';
                            }
                        }
                        
                        \DB::table('users')->where('login', '=', $user_array['login'])->update(['user_is_playing' => 0]);
                        \DB::table('users')->where('login', '=', $opponent_array['login'])->update(['user_is_playing' => 0]);
                        
                        $result = ['message' => 'gameEnds', 'gameResult' => $game_result, 'battleInfo' => $msg->ident->battleId];
                        self::sendMessageToSelf($from, $result);
                        self::sendMessageToOthers($from, $result, $this->battles[$msg->ident->battleId]);
                        
                    }else{
                        \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_member_id'])->update([
                        'user_discard'  => serialize($user_array['user_discard']),
                        'user_hand'     => serialize($user_array['user_hand']),
                        'user_deck'     => serialize($user_array['user_deck'])
                        ]);
                        \DB::table('tbl_battle_members')->where('id', '=', $opponent_array['battle_member_id'])->update([
                            'user_discard'  => serialize($opponent_array['user_discard']),
                            'user_hand'     => serialize($opponent_array['user_hand']),
                            'user_deck'     => serialize($opponent_array['user_deck'])
                        ]);

                        //Обнуление значений пасования раундов
                        foreach($battle_members as $user_iter => $battle_data){
                            \DB::table('tbl_battle_members')->where('id', '=', $battle_data->id)->update(['round_passed' => 0]);
                        }

                        self::sendUserMadeActionData($msg, $user_array, $opponent_array, $battle_field, $magic_usage, 'hand', [], $user_turn, $from, $SplBattleObj);
                    }
                }
                break;

                
            //Пользователь сделал действие
            case 'userMadeCardAction':
                echo date('Y-m-d H:i:s')."\n";
                if($battle -> fight_status == 2){
                    //Данные о текущем пользователе
                    $battle_field = unserialize($battle->battle_field);//Данные о поле битвы
                    //Установка источника хода по умолчанию
                    $card_source = 'hand';
                    //Отыгрыш карт по умолчанию
                    $card_to_play = [];
                    //Использованые Маг. Эффекты
                    $magic_usage = unserialize($battle->magic_usage);
                    
                    //определение очереди хода
                    if($opponent_array['round_passed'] == 1){
                        $user_turn = $user_array['login'];
                        $user_turn_id = $user_array['id'];
                    }else{
                        $user_turn = $opponent_array['login'];
                        $user_turn_id = $opponent_array['id'];
                    }
                    
                    if(empty($opponent_array['user_hand'])){
                        self::userPassed($opponent_array['id'], $msg->ident->battleId);
                    }
                    
                    //Обработка магических еффектов
                    if(isset($msg->magic)){
                        $magic_id = Crypt::decrypt($msg->magic);
                        
                        $magic = json_decode(SiteGameController::getMagicData($magic_id));//Получаем данные о маг. эффекте (далее МЭ)

                        $energy = $user_array['energy'] - $magic->energy_cost;//Кол-во энергии пользователя после использования МЭ
                        
                        if( (!in_array($msg->magic, $magic_usage)) && ($energy > 0) ){
                            //Сохранение значений энергии 
                            \DB::table('tbl_user_data')->where('user_id','=',$user_array['id'])->update(['user_energy' => $energy]);
                            $user_array['energy'] = $energy;

                            //Если МЭ существует и кол-во его использований больше 0
                            if( (isset($user_array['magic_effects'][$magic_id])) && ($user_array['magic_effects'][$magic_id] > 0) ){
                                $user_array['magic_effects'][$magic_id]--;//Уменьшения показателя использования МЭ
                                
                                $magic_usage[$user_array['player']][] = $msg->magic;//Сохраняем МЭ в использованых
                                $battle->magic_usage = serialize($magic_usage);
                                $battle->save();

                                foreach($magic->actions as $action_iter => $action_data){
                                    switch($action_data->action){
                                        //ПРИЗЫВ КАРТЫ
                                        case '2':
                                            if($action_data->MAplayCardsFromDeck_cardType == 0){//Выбрать определенную карту
                                                //Поиск доступных карт для применения
                                                for($i=0; $i<count($user_array['user_deck']); $i++){
                                                    if(in_array(Crypt::decrypt($user_array['user_deck'][$i]['id']), $action_data->currentCard)){
                                                        $card_to_play[] = $i;
                                                    }
                                                }
                                            }else{//Выбрать карту определенного ряда
                                                foreach($action_data->MAplayCardsFromDeck_ActionRow as $row_iter => $row){
                                                    for($i=0; $i<count($user_array['user_deck']); $i++){
                                                        if(in_array($row, $user_array['user_deck'][$i]['action_row'])){
                                                            if($user_array['user_deck'][$i]['type'] != 'special'){
                                                                $card_to_play[] = $i;
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                            if(!empty($card_to_play)){
                                                $user_turn = $user_array['login'];
                                                $user_turn_id = $user_array['id'];
                                                $card_source = 'deck';
                                            }
                                        break;
                                        //END OF ПРИЗЫВ КАРТЫ
                                        
                                        //ОТМЕНА НЕГАТИВНЫХ ЭФФЕКТОВ
                                        case '3':
                                            $temp_heal_action = self::makeHealToMid($battle_field['mid'], [0,1,2], $user_array, $opponent_array);
                                            $battle_field['mid'] = $temp_heal_action['battle_field_mid'];
                                            $user_array['user_discard'] = $temp_heal_action['user_array_discard'];
                                            $opponent_array['user_discard'] = $temp_heal_action['opponent_array_discard'];
                                        break;
                                        //END OF ОТМЕНА НЕГАТИВНЫХ ЭФФЕКТОВ
                                    }
                                }
                            }
                        }
                    }
                        
                    //Обработка Карты и её действий
                    if(isset($msg->card)){
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

                                    //Если карт на поле спец карт больше 6ти
                                    if(count($battle_field['mid']) > 6){
                                        //Кидает первую карту в отбой
                                        if($user_array['login'] == $battle_field['mid'][0]['login']){
                                            $user_array['user_discard'][] = self::transformObjToArr($battle_field['mid'][0]['card']);
                                        }else{
                                            $opponent_array['user_discard'][] = self::transformObjToArr($battle_field['mid'][0]['card']);
                                        }
                                        //Удаляем первую карту
                                        unset($battle_field['mid'][0]);
                                    }
                                    //Добавляем текущую карту на поле боя и её принадлежность пользователю
                                    $battle_field['mid'] = array_values($battle_field['mid']);
                                }else{
                                    //Если логика карт предусматривает сразу уходить в отбой
                                    foreach($card->actions as $i => $action){
                                        if( (($action->action == '13')&&($card->type == 'special'))||($action->action == '24')||($action->action == '27')||($action->action == '29')){
                                            $user_array['user_discard'][] = self::transformObjToArr($card);
                                        }else{
                                            //Еcли в ряду уже есть спец карта
                                            if(!empty($battle_field[$user_battle_field_identificator][$field_row]['special'])){
                                                if($battle_field[$user_battle_field_identificator][$field_row]['special']['login'] == $user_array['login']){
                                                    $user_array['user_discard'][] = self::transformObjToArr($battle_field[$user_battle_field_identificator][$field_row]['special']['card']);
                                                }else{
                                                    $opponent_array['user_discard'][] = self::transformObjToArr($battle_field[$user_battle_field_identificator][$field_row]['special']['card']);
                                                }
                                            }
                                            $battle_field[$user_battle_field_identificator][$field_row]['special'] = ['card' => $card, 'strength' => $card->strength, 'login' => $user_array['login']];
                                        }
                                    }
                                }
                            //Если карта относится к картам воинов 
                            }else{
                                $battle_field[$user_battle_field_identificator][$field_row]['warrior'][] = ['card'=>  self::transformObjToArr($card), 'strength'=>$card->strength, 'login' => $user_array['login']];
                            }
                            //Убираем карту из текущй колоды
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
                            }
                            //Перебор действий карты
                            foreach($card->actions as $action_iter => $action_data){
                                switch($action_data->action){
                                    //ШПИОН
                                    case '12':
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
                                    break;
                                    //END OF ШПИОН
                                    
                                    //УБИЙЦА
                                    case '13':
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
                                                            $card_data['card'] = self::transformObjToArr($card_data['card']);

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
                                                            $card_data['card'] = self::transformObjToArr($card_data['card']);

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
                                                    $card_data['card'] = self::transformObjToArr($card_data['card']);

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
                                    break;
                                    //END OF УБИЙЦА
                                    
                                    //РАЗВЕДЧИК
                                    case '15':
                                        $deck_card_count = count($user_array['user_deck']);
                                        if($deck_card_count > 0){
                                            $rand_item = rand(0, $deck_card_count-1);
                                            $random_card = $user_array['user_deck'][$rand_item];
                                            $user_array['user_hand'][] = $random_card;
                                            unset($user_array['user_deck'][$rand_item]);

                                            $user_array['user_deck'] = array_values($user_array['user_deck']);
                                        }
                                    break;
                                    //END OF РАЗВЕДЧИК
                                    
                                    //CТРАШНЫЙ
                                    case '21':
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
                                    break;
                                    //END OF CТРАШНЫЙ
                                    
                                    //ПОВЕЛИТЕЛЬ
                                    case '22':
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
                                    break;
                                    //END OF ПОВЕЛИТЕЛЬ
                                    
                                    //ИСЦЕЛЕНИЕ
                                    case '25':
                                        $temp_heal_action = self::makeHealToMid($battle_field['mid'], [$field_row], $user_array, $opponent_array);
                                        $battle_field['mid'] = $temp_heal_action['battle_field_mid'];
                                        $user_array['user_discard'] = $temp_heal_action['user_array_discard'];
                                        $opponent_array['user_discard'] = $temp_heal_action['opponent_array_discard'];
                                    break;
                                    //END OF ИСЦЕЛЕНИЕ
                                    
                                    //ОДУРМАНИВАНИЕ
                                    case '23':
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
                                    break;
                                    //END OF ОДУРМАНИВАНИЕ

                                    //ПЕРЕГРУППИРОВКА
                                    case '24':
                                    foreach($battle_field[$msg->player][$field_row]['warrior'] as $i => $card_data){
                                        if($card_data['card']->id == $msg->retrieve){
                                            unset($battle_field[$msg->player][$field_row]['warrior'][$i]);
                                            $battle_field[$msg->player][$field_row]['warrior'] = array_values($battle_field[$msg->player][$field_row]['warrior']);
                                            $user_array['user_hand'][] = self::transformObjToArr($card_data['card']);
                                        }
                                    }
                                    break;
                                    //END OF ПЕРЕГРУППИРОВКА
                                    
                                    //ПЕЧАЛЬ
                                    case '26':
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
                                    break;
                                    //END OF ПЕЧАЛЬ
                                    
                                    //ПРИЗЫВ
                                    case '27':
                                        $allow_change_turn = 0;
                                        if(!empty($user_array['user_deck'])){
                                            foreach($user_array['user_deck'] as $i => $card_data){
                                                $card_data = self::transformObjToArr($card_data);
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
                                    break;
                                    //END OF ПРИЗЫВ
                                    
                                    //ЛЕКАРЬ
                                    case '29':
                                        $allow_change_turn = 0;
                                        if(!empty($user_array['user_discard'])){
                                            foreach($user_array['user_discard'] as $i => $card_data){
                                                $card_data = self::transformObjToArr($card_data);
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
                                    break;
                                    //END OF ЛЕКАРЬ
                                }
                            }
                            //END OF Перебор действий карты
                        }
                    }

                    $battle_field = self::recalculateStrengthByMid($battle_field, $user_array, $opponent_array);

                    if(count($user_array['user_hand']) == 0){//Если у пользлвателя закончились карты на руках - делаем ему автопас
                        \DB::table('tbl_battle_members')->where('id', '=', $msg->ident->battleId)->update(['round_passed' => '1']);
                    }

                    $user_array['user_deck'] = array_values($user_array['user_deck']);
                    $user_array['user_hand'] = array_values($user_array['user_hand']);
                    $user_array['user_discard'] = array_values($user_array['user_discard']);

                    $opponent_array['user_deck'] = array_values($opponent_array['user_deck']);
                    $opponent_array['user_hand'] = array_values($opponent_array['user_hand']);
                    $opponent_array['user_discard'] = array_values($opponent_array['user_discard']);
                    
                    //Сохраняем руку, колоду и отбой опльзователя
                    \DB::table('tbl_battle_members')->where('id', '=', $user_array['battle_member_id'])->update([
                        'user_deck'     => serialize($user_array['user_deck']),
                        'user_hand'     => serialize($user_array['user_hand']),
                        'user_discard'  => serialize($user_array['user_discard']),
                        'card_source'   => $card_source,
                        'card_to_play'  => serialize($card_to_play)
                    ]);
                    \DB::table('tbl_battle_members')->where('id', '=', $opponent_array['battle_member_id'])->update([
                        'user_deck'     => serialize($opponent_array['user_deck']),
                        'user_hand'     => serialize($opponent_array['user_hand']),
                        'user_discard'  => serialize($opponent_array['user_discard'])
                    ]);
                    //Сохраняем поле битвы
                    $battle->battle_field = serialize($battle_field);
                    $battle->user_id_turn = $user_turn_id;
                    $battle->save();
                    
                    self::sendUserMadeActionData($msg, $user_array, $opponent_array, $battle_field, $magic_usage, $card_source, $card_to_play, $user_turn, $from, $SplBattleObj);
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
    //Socket actions end
    
    
    protected static function dropCardFromDeck($deck, $card){
        $deck = array_values($deck);
        //Количество карт в входящей колоде
        $deck_card_count = count($deck);
        for($i=0; $i<$deck_card_count; $i++){
            if(!is_array($deck[$i])){
                $deck[$i] = self::transformObjToArr($deck[$i]);
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
                        $card_array_data = self::transformObjToArr($card_data['card']);
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

                        $card_data['card'] = self::transformObjToArr($card_data['card']);

                        foreach($card_data['card']['actions'] as $j => $action){
                            $action = self::transformObjToArr($action);
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
                    $card_data['card'] = self::transformObjToArr($card_data['card']);
                    foreach($card_data['card']['actions']as $j => $action){
                        $action = self::transformObjToArr($action);
                        if($action['action'] == '21'){
                            if(!isset($actions_array_fear[Crypt::decrypt($card_data['card']['id'])])){
                                $actions_array_fear['mid'][Crypt::decrypt($card_data['card']['id'])] = $card_data;
                            }
                        }
                    }
                }
            }
        }
        
        //Применение действия "Страшный" к картам
        foreach($actions_array_fear as $source => $cards){

            foreach($cards as $card_id => $card_data){
                $card_data = self::transformObjToArr($card_data);

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
        }

        //Применение "Поддержка" к картам
        foreach($actions_array_support as $card_id => $card_data){
            $card_data['card'] = self::transformObjToArr($card_data['card']);

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
                    $card['card'] = self::transformObjToArr($card['card']);

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
        }

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
                        $allow_fury_by_magic = true;//!!!!!ДОДЕЛАТЬ
                    }else{
                        $allow_fury_by_magic = true;
                    }

                    if(($allow_fury_by_row) && ($allow_fury_by_race) && ($allow_fury_by_magic)){
                        $card_destignation = explode('_',$card_id);
                        $battle_field[$card_destignation[0]][$card_destignation[1]]['warrior'][$card_destignation[2]]['strength'] += $action->CAfury_addStrenght;
                    }
                }
            }
        }

        //Применение "Боевое братство" к картам
        $cards_to_brotherhood = [];
        foreach($actions_array_brotherhood as $player => $cards_array){
            foreach($cards_array as $card_id => $card_data){
                $card_data['card'] = self::transformObjToArr($card_data['card']);

                foreach($card_data['card']['actions'] as $action_iter => $action){
                    $action = self::transformObjToArr($action);

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
        }

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
        }

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
        }

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
    
    
    protected static function userPassed($user_id, $battle_id){
        \DB::table('tbl_battle_members')->where('battle_id', '=', $battle_id)->where('user_id', '=', $user_id)->update(['round_passed'=> 1]);
        return \DB::table('tbl_battle_members')->where('battle_id', '=', $battle_id)->where('round_passed', '=', 1)->count();
    }
    
    
    protected static function cardsToLeft($battle_field, $player){
        $rows_to_card_left = [];
        $cards_to_left = [];
        foreach($battle_field[$player] as $row => $row_data){
            if(!empty($row_data['warrior'])){
                $rows_to_card_left[] = $row;
            }
        }
        foreach($rows_to_card_left as $row){
            foreach($battle_field[$player][$row]['warrior'] as $card_iter => $card_data){
                $allow_to_count = true;
                
                foreach($card_data['card']['actions'] as $action_iter => $action){
                    if($action->action == '20'){
                        $allow_to_count = false;
                    }
                }
                if($allow_to_count){
                    $cards_to_left[] = [$player.'_'.$row.'_'.$card_iter => $card_data];
                }
            }
        }
        
        if(!empty($cards_to_left)){
            $card_to_left = $cards_to_left[rand(0, count($cards_to_left)-1)];
        }else{
            $card_to_left = [];
        }
        return $card_to_left;
    }
    
    
    public static function transformObjToArr($card){
        if(!is_array($card)){
            $card = get_object_vars($card);
        }
        return $card;
    }


    
    public static function sendUserMadeActionData($msg, $user_array, $opponent_array, $battle_field, $magic_usage, $card_source, $card_to_play, $user_turn, $from, $SplBattleObj){
        $user_discard_count = count($user_array['user_discard']);
        $user_deck_count = count($user_array['user_deck']);

        $oponent_discard_count = count($opponent_array['user_discard']);
        $oponent_deck_count = count($opponent_array['user_deck']);

        $result = [
            'message'       => 'userMadeAction',
            'field_data'    => $battle_field,
            'user_hand'     => $user_array['user_hand'],
            'user_deck'     => $user_array['user_deck'],
            'user_discard'  => $user_array['user_discard'],
            'counts'        => [
                'user_deck'    => $user_deck_count,
                'user_discard' => $user_discard_count,
                'opon_discard' => $oponent_discard_count,
                'opon_deck'    => $oponent_deck_count
            ],
            'battleInfo'    => $msg->ident->battleId,
            'login'         => $user_turn,
            'cardSource'    => $card_source,
            'cardToPlay'    => $card_to_play,
            'magicUsage'    => $magic_usage[$user_array['player']]
        ];
        self::sendMessageToSelf($from, $result); //Отправляем результат отправителю

        $result = [
            'message'       => 'userMadeAction',
            'field_data'    => $battle_field,
            'user_hand'     => $opponent_array['user_hand'],
            'user_discard'  => $opponent_array['user_discard'],
            'counts'        => [
                'user_deck'    => $oponent_deck_count,
                'user_discard' => $oponent_discard_count,
                'opon_discard' => $user_discard_count,
                'opon_deck'    => $user_deck_count
            ],
            'battleInfo'    => $msg->ident->battleId,
            'login'         => $user_turn
        ];
        self::sendMessageToOthers($from, $result, $SplBattleObj[$msg->ident->battleId]);
    }
    
    
    protected static function userGainCards($array){
        $card_to_gain = ($array['current_deck'] == 'knight') ? 2: 1;
        if(count($array['user_deck']) < $card_to_gain) $card_to_gain = count($array['user_deck']);
        
        for($i=0; $i<$card_to_gain; $i++){
            if(!empty($array['user_deck'])){
                $rand = rand(0, count($array['user_deck'])-1);
                $array['user_hand'][] = $array['user_deck'][$rand];
                unset($array['user_deck'][$rand]);
                $array['user_deck'] = array_values($array['user_deck']);
            }
        }
        return $array;
    }
    
    protected static function makeHealToMid($battle_field_mid, $field_row, $user_array, $opponent_array){
        foreach($battle_field_mid as $i => $card_data){
            foreach($card_data['card']->actions as $action_iterrator => $action){
                $allow_to_drop_from_mid = false;
                if($action->action == '21'){
                    for($j=0; $j<count($field_row); $j++){
                        if(in_array($field_row[$j], $action->CAfear_ActionRow)){
                            $allow_to_drop_from_mid = true;
                        }
                    }

                    if($allow_to_drop_from_mid){
                        if($user_array['login'] == $card_data['login']){
                            $user_array['user_discard'][] = self::transformObjToArr($card_data['card']);
                        }else{
                            $opponent_array['user_discard'][] = self::transformObjToArr($card_data['card']);
                        }
                        unset($battle_field_mid[$i]);
                    }
                }
            }
        }
        $battle_field_mid = array_values($battle_field_mid);
        return [
            'battle_field_mid'  => $battle_field_mid,
            'user_array_discard'=> $user_array['user_discard'],
            'opponent_array_discard' => $opponent_array['user_discard']
        ];
    }
}