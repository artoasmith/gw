<?php
namespace App\Http\Controllers\Site;

use App\BattleLogModel;
use App\BattleModel;
use App\BattleMembersModel;
use App\CardsModel;
use App\EtcDataModel;
use App\LeagueModel;
use App\MagicEffectsModel;
use App\RaceModel;
use App\User;
use App\UserAdditionalDataModel;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteGameController extends BaseController
{
    /*
     * $user_id - ID текущего пользователя
     * $user_deck - текущая колода пользователя
     * $players_quantity - максимальное количествоигроков за столом
     * $deck_weight - сила колоды
     * $league - лига
     * */
    protected static function battleGetUserSettings($user_data, $current_deck){

        //Карты текущей колоды
        $user_deck = unserialize($user_data->user_cards_in_deck)[$current_deck];

        //Активное волшебство пользователя
        $user_magic = [];
        if($user_data->user_magic_effects == ''){
            $magic_effects = [];
        }else{
            $magic_effects = unserialize($user_data->user_magic_effects);
        }

        foreach ($magic_effects as $key => $value){
            if($value['active'] == 1){

                $current_magic_effect = \DB::table('tbl_magic_effects')->select('id', 'race')->where('id', '=', $key)->get();

                $current_magic_effect_races = unserialize($current_magic_effect[0]->race);
                if(in_array($current_deck, $current_magic_effect_races, true)){
                    $user_magic[$key] = $value['used_times'];
                }

            }
        }

        return ['deck' => $user_deck, 'magic_effects' => $user_magic];
    }



    //Изменение данных пользовотеля об участии в столах
    protected static function updateBattleMembers($user_id, $battle_id, $user_deck_race, $user_deck, $user_magic, $user_energy){
        //Ищем пользователя в таблице tbl_battle_memebers
        $user_is_battle_member = BattleMembersModel::where('user_id', '=', $user_id)->get();

        //Настройки колоды
        $maxCardDeck = \DB::table('tbl_etc_data')->select('meta_key', 'meta_value')->where('meta_key', '=', 'maxCardQuantity')->get();

        //Колода пользователя
        $user_deck = unserialize($user_deck);

        //Создание массива всех карт в колоде по отдельности (без указания колличества)
        $real_card_array = [];
        foreach ($user_deck as $card_id => $cards_quantity){
            for($i = 0; $i<$cards_quantity; $i++){
                $real_card_array[] = ['id' => $card_id];
            }
        }

        $deck_card_count = count($real_card_array);
        //Создание масива игральной колоды
        $user_deck = [];
        while(count($user_deck) != $maxCardDeck[0]->meta_value){
            //Случайный индекс карты колоды
            $rand_item = rand(0, $deck_card_count-1);
            //Перенос карты в колоду
            $user_deck[] = $real_card_array[$rand_item];
            //Убираем данную карту из массива всех карт
            unset($real_card_array[$rand_item]);

            $real_card_array = array_values($real_card_array);
            $deck_card_count = count($real_card_array);
        }

        //Настройки колоды "Руки"
        $maxHandCardQuantity = \DB::table('tbl_etc_data')->select('meta_key', 'meta_value')->where('meta_key', '=', 'maxHandCardQuantity')->get();

        //Карты руки пользователя
        $user_hand = [];
        //Количество карт в колоде
        $deck_card_count = count($user_deck);

        //Создание массива карт руки (случайный выбор)
        while(count($user_hand) != $maxHandCardQuantity[0] -> meta_value){
            //Случайный индекс карты колоды
            $rand_item = rand(0, $deck_card_count-1);
            //Перенос карты в колоду руки
            $user_hand[] = $user_deck[$rand_item];

            //Убираем данную карту из колоды
            unset($user_deck[$rand_item]);

            //Пересчет колоды
            $user_deck = array_values($user_deck);
            $deck_card_count = count($user_deck);
        }

        $user_deck = self::buildCardDeck($user_deck, []);
        $user_hand = self::buildCardDeck($user_hand, []);

        //Если пользователя не сучествует в табице tbl_battle_members
        if( 1 > count($user_is_battle_member) ){
            $result = BattleMembersModel::create([
                'user_id'       => $user_id,
                'battle_id'     => $battle_id,
                'user_deck_race'=> $user_deck_race,
                'user_deck'     => serialize($user_deck),
                'user_hand'     => serialize($user_hand),
                'magic_effects' => $user_magic,
                'user_energy'   => $user_energy,
                'user_ready'    => 0,
                'round_passed'  => 0,
                'rounds_won'    => 0
            ]);
        }else{
            $user_battle = BattleMembersModel::find($user_is_battle_member[0]->id);
            $user_battle -> battle_id       = $battle_id;
            $user_battle -> user_deck_race  = $user_deck_race;
            $user_battle -> user_deck       = serialize($user_deck);
            $user_battle -> user_hand       = serialize($user_hand);
            $user_battle -> magic_effects   = $user_magic;
            $user_battle -> user_energy     = $user_energy;
            $user_battle -> user_ready      = 0;
            $user_battle -> round_passed    = 0;
            $user_battle -> rounds_won      = 0;
            $result =  $user_battle -> save();
        }
        return $result;
    }


    //Создание стола
    protected function createTable(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        //Проверка: количество играков >= 2 и <= 8; количество играков парное
        if (($data['players'] % 2 == 0) && ($data['players'] <= 8) && ($data['players'] >= 2)) {
            $user = Auth::user();

            //Силиа колоды
            $deck_weight = base64_decode($data['deck_weight']);
            //Лига
            $league = base64_decode($data['league']);

            //Вторичные данные пользователя
            $user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

            $user_settings = self::battleGetUserSettings($user_data[0], $user['user_current_deck']);

            $result = BattleModel::create([
                'creator_id'        => $user['id'],
                'players_quantity'  => $data['players'],
                'deck_weight'       => $deck_weight,
                'league'            => $league,
                'fight_status'      => 0,
                'player_num_turn'   => 0,
                'round_status'      => serialize(['enemy'=>[], 'alias'=>[]]),
            ]);

            if($result === false){
                return json_encode(['message' => 'Не удалось создать стол']);
            }

            BattleMembersModel::where('user_id', '=', $user['id'])->update(['user_ready' => 0]);

            //Создание данных об участниках битвы
            $battle_members = self::updateBattleMembers(
                $user['id'],
                $result->id,
                $user['user_current_deck'],
                serialize($user_settings['deck']),
                serialize($user_settings['magic_effects']),
                $user_data[0]->user_energy
            );

            if($battle_members === false){
                $dropBattle = BattleModel::find($result->id);
                $dropBattle -> delete();
                return json_encode(['message' => 'Не удалось настройки стола']);
            }

            //Лог боя
            $battle_log_result = BattleLogModel::create([
                'battle_id' => $result -> id,
                'fight_log' => '<p>Стол № '.$result -> id.' создан плльзователем '.$user['login'].'(ID = '.$user['id'].') </p>'
            ]);

            //Отмечаем что пользователь уже играет
            $user_is_playing = User::find($user['id']);
            $user_is_playing -> user_is_playing = 1;
            $user_is_playing -> save();

            if($battle_log_result !== false){
                return redirect(route('user-in-game', ['game' => $result->id]));
            }
        }
    }


    //Пользователь присоединился к столу
    protected function userConnectToBattle(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        $user = Auth::user();
        //Данные о столе
        $battle_data = BattleModel::find($data['id']);

        if($battle_data->creator_id == $user['id']){
            return json_encode(['message' => 'success']);
        }

        //Если стол не пользовательский

        $users_count_in_battle = BattleMembersModel::where('battle_id', '=', $battle_data->id)->count();

        //Если стол уже занят
        if($users_count_in_battle >= $battle_data->players_quantity) {
            return json_encode(['message' => 'success']);
        }

        //Если не занят
        $user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

        $user_settings = self::battleGetUserSettings($user_data[0], $user['user_current_deck']);

        $battle_members = self::updateBattleMembers(
            $user['id'],
            $battle_data->id,
            $user['user_current_deck'],
            serialize($user_settings['deck']),
            serialize($user_settings['magic_effects']),
            $user_data[0]->user_energy
        );

        if ($battle_members === false) {
            return json_encode(['message' => 'Не удалось подключится к столу.']);
        }

        $battle_log_result = BattleLogModel::where('battle_id','=',$battle_data->id)->get();

        $fight_log_str = $battle_log_result[0] -> fight_log.'<p>К столу № '.$battle_data->id.' присоединился плльзователь '.$user['login'].'(ID = '.$user['id'].') </p>';

        $fight_log_to_update = BattleLogModel::find($battle_log_result[0]->id);
        $fight_log_to_update -> fight_log = $fight_log_str;
        $fight_log_to_update -> save();

        //Отмечаем что пользователь уже играет
        $user_is_playing = User::find($user['id']);
        $user_is_playing -> user_is_playing = 1;
        $user_is_playing -> save();

        return json_encode(['message' => 'success']);
    }


    //Создание колод по id карт
    protected static function buildCardDeck($deck, $result_array){
        foreach($deck as $key => $card_id){
            $card_data = \DB::table('tbl_card')->select('id','title','slug','card_type','card_strong','img_url','short_description', 'allowed_rows', 'card_actions')->where('id', '=', $card_id['id'])->get();

            $result_array[] = [
                'id'        => $card_data[0]->id,
                'title'     => $card_data[0]->title,
                'slug'      => $card_data[0]->slug,
                'type'      => $card_data[0]->card_type,
                'strength'  => $card_data[0]->card_strong,
                'img_url'   => $card_data[0]->img_url,
                'descript'  => $card_data[0]->short_description,
                'action_row'=> unserialize($card_data[0]->allowed_rows),
                'actions'   => unserialize($card_data[0]->card_actions)
            ];
        }
        return $result_array;
    }


    //Подготовка к бою закончена
    //Выдача колод пользователей
    protected function startGame(Request $request){
        $data = $request->all();

        $current_user = Auth::user(); //Данные текущего пользователя

        $battle_members = BattleMembersModel::where('battle_id', '=', $data['battle_id'])->get(); //Данные текущей битвы

        $users_result_data = [];

        foreach($battle_members as $key => $value){

            $user = \DB::table('users')->select('id','login','img_url')->where('id', '=', $value -> user_id)->get();// Пользователи участвующие в битве

            $current_user_deck_race = \DB::table('tbl_race')->select('title', 'slug')->where('slug','=', $value -> user_deck_race)->get(); //Название колоды

            $user_current_deck = unserialize($value -> user_deck); //Карты колоды пользователя
            $user_current_hand = unserialize($value -> user_hand); //Карты руки пользователя

            $deck = [];
            $hand = [];

            //Если участник битвы - противник
            if($current_user['id'] != $user[0]->id){
                $deck_card_count = count($user_current_deck); //Колличелство карт колоды
                $available_to_change = 0; //Количество карт, что может заменить
            }else{
                $deck = self::buildCardDeck($user_current_deck, $deck); //Создание массива карт колоды
                $hand = self::buildCardDeck($user_current_hand, $hand); //Создание массива карт руки
                $deck_card_count = count($deck);//Колличелство карт колоды

                //Если пользователь принадлежит к расе гномов
                if($value -> user_deck_race == 'highlander'){
                    $available_to_change = 4;
                }else{
                    $available_to_change = 2;
                }
            }

            //Магические эффекты пользователя (волшебство)
            $user_magic_effect_data = [];
            $magic_effects = unserialize($value->magic_effects);

            foreach($magic_effects as $id => $actions){
                $magic_effect_data = \DB::table('tbl_magic_effects')->select('id', 'title', 'slug', 'img_url', 'description', 'energy_cost')->where('id', '=', $id)->get();
                $user_magic_effect_data[] = [
                    'id'            => $id,
                    'title'         => $magic_effect_data[0]->title,
                    'slug'          => $magic_effect_data[0]->slug,
                    'img_url'       => $magic_effect_data[0]->img_url,
                    'description'   => $magic_effect_data[0]->description,
                    'energy_cost'   => $magic_effect_data[0]->energy_cost,
                ];
            }

            $users_result_data[$user[0]->login] = [
                'img_url'   => $user[0]->img_url,
                'deck_slug' => $value -> user_deck_race,
                'deck_title'=> $current_user_deck_race[0]->title,
                'deck'      => [],
                'deck_count'=> $deck_card_count,
                'hand'      => $hand,
                'magic'     => $user_magic_effect_data,
                'energy'    => $value -> user_energy,
                'ready'     => $value -> user_ready,
                'can_change_cards'  => $available_to_change
            ];
        }

        return json_encode(['message' => 'success', 'userData' => $users_result_data]);
    }


    protected function userChangeCards(Request $request){
        $data = $request->all();

        $user = Auth::user();

        $cards_to_change = json_decode($data['cards']); //Карты что будут заменены
        $cards_to_change_count = count($cards_to_change); //Количество карт для замены

        $user_battle = \DB::table('tbl_battle_members')->select('id', 'user_id', 'battle_id', 'user_deck', 'user_hand')->where('user_id', '=', $user['id'])->get(); //Данные текущей битвы пользователя

        $user_hand = unserialize($user_battle[0]->user_hand); //Карты руки пользователя

        for($i=0; $i<$cards_to_change_count; $i++){
            foreach($user_hand as $key => $value){
                if($value['id'] == $cards_to_change[$i]){
                    unset($user_hand[$key]); //Удаляем заменяемые карты из руки
                    break;
                }
            }
        }

        $user_hand = array_values($user_hand);

        $user_deck = unserialize($user_battle[0]->user_deck); //Колода игрока
        $deck_card_count = count($user_deck);

        for($i=0; $i<$cards_to_change_count; $i++){ //перемещаем N рандомных карт из колоды в руку
            $rand_item = rand(0, $deck_card_count-1);
            $user_hand[] = $user_deck[$rand_item];

            unset($user_deck[$rand_item]);

            $user_deck = array_values($user_deck);
            $deck_card_count = count($user_deck);
        }

        for($i=0; $i<$cards_to_change_count; $i++){ //Заменяемые карты возвращаем обратко в колоду
            $user_deck[] = ['id' => $cards_to_change[$i]];
        }
        $deck_card_count = count($user_deck);

        $hand = self::buildCardDeck($user_hand, []);
        $deck = self::buildCardDeck($user_deck, []);

        $users_result_data[$user['login']] = [
            'deck_count'=> $deck_card_count,
            'hand'      => $hand,
            'deck'      => $deck
        ];

        $battle_log = BattleLogModel::where('battle_id', '=', $user_battle[0] -> battle_id)->get();
        $fight_log = $battle_log[0]->fight_log.'<p>Пользователь '.$user['login'].'(ID='.$user['id'].' Сел за стол № '.$user_battle[0] -> battle_id.' с колодой: '.json_encode($deck).')</p>';
        BattleLogModel::where('battle_id', '=', $user_battle[0] -> battle_id)->update(['fight_log' => $fight_log]);

        $hand = serialize($hand);
        $deck = serialize($deck);

        $user_battle_to_update = BattleMembersModel::find($user_battle[0]->id); //Сохраняем колоды
        $user_battle_to_update -> user_hand = $hand;
        $user_battle_to_update -> user_deck = $deck;
        $user_battle_to_update -> user_ready = 1;
        $user_battle_to_update -> save();

        return json_encode($users_result_data);
    }



    public function socketSettings(){
        $user = Auth::user();

        $battle_member = \DB::table('tbl_battle_members')->select('battle_id','user_id')->where('user_id', '=', $user['id'])->get();

        $sec = intval(getenv('GAME_SEC_TIMEOUT'));
        if($sec<=0){
            $sec = 60;
        }

        return json_encode([
            'battle'    => $battle_member[0]->battle_id,
            'user'      => $user['id'],
            'hash'      => md5(getenv('SECRET_MD5_KEY').$user['id']),
            'dom'       => getenv('APP_DOMEN_NAME'),
            'timeOut'   => $sec
        ]);
    }
}
