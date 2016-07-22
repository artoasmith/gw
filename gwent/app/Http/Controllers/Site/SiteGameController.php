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
                $user_magic[$key] = $value['used_times'];
            }
        }

        return ['deck' => $user_deck, 'magic_effects' => $user_magic];
    }

    public static function updateBattleMembers($user_id, $battle_id, $user_deck_race, $user_deck, $user_magic, $user_energy){
        $user_is_battle_member = BattleMembersModel::where('user_id', '=', $user_id)->get();

        if( 1 > count($user_is_battle_member) ){
            $result = BattleMembersModel::create([
                'user_id'       => $user_id,
                'battle_id'     => $battle_id,
                'user_deck_race'=> $user_deck_race,
                'user_deck'     => $user_deck,
                'magic_effects' => $user_magic,
                'user_energy'   => $user_energy
            ]);
        }else{
            $user_battle = BattleMembersModel::find($user_is_battle_member[0]->id);
            $user_battle -> battle_id       = $battle_id;
            $user_battle -> user_deck_race  = $user_deck_race;
            $user_battle -> user_deck       = $user_deck;
            $user_battle -> magic_effects   = $user_magic;
            $user_battle -> user_energy     = $user_energy;
            $result =  $user_battle -> save();
        }
        return $result;
    }

    protected function createTable(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        if (($data['players'] % 2 == 0) && ($data['players'] <= 8) && ($data['players'] >= 2)) {
            $user = Auth::user();

            $deck_weight = base64_decode($data['deck_weight']);
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

    protected function userConnectToBattle(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        $user = Auth::user();
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


    //Подготовка к бою закончена
    //Выдача колод пользователей
    protected function startGame(Request $request){
        $data = $request->all();

        $battle_members = BattleMembersModel::where('battle_id', '=', $data['battle_id'])->get();

        $users_result_data = [];
        foreach($battle_members as $key => $value){
            $user = \DB::table('users')->select('id','login','img_url')->where('id', '=', $value -> user_id)->get();

            $current_user_deck_race = \DB::table('tbl_race')->select('title', 'slug')->where('slug','=', $value -> user_deck_race)->get();
            $user_current_full_deck = unserialize($value -> user_deck);

            $deck = [];
            foreach($user_current_full_deck as $card_id => $cards_quantity){
                for($i = 0; $i<$cards_quantity; $i++){

                    $card_data = \DB::table('tbl_card')->select('id','title','slug','card_type','card_strong','img_url','short_description')->where('id', '=', $card_id)->get();

                    $deck[] = [
                        'id'        => $card_id,
                        'title'     => $card_data[0]->title,
                        'slug'      => $card_data[0]->slug,
                        'type'      => $card_data[0]->card_type,
                        'strength'  => $card_data[0]->card_strong,
                        'img_url'   => $card_data[0]->img_url,
                        'descript'  => $card_data[0]->short_description
                    ];
                }
            }

            $hand = [];

            $deck_card_count = count($deck);

            while(count($hand) != 11){
                $rand_item = rand(0, $deck_card_count-1);
                $hand[] = $deck[$rand_item];

                unset($deck[$rand_item]);

                $deck = array_values($deck);
                $deck_card_count = count($deck);
            }

            $users_result_data[] = [
                'login'     => $user[0]->login,
                'img_url'   => $user[0]->img_url,
                'deck_slug' => $value -> user_deck_race,
                'deck_title'=> $current_user_deck_race[0]->title,
                'deck'      => $deck,
                'hand'      => $hand,
                'magic'     => unserialize($value->magic_effets),
                'energy'    => $value -> user_energy
            ];
        }

        return json_encode(['message' => 'success', 'userData' => $users_result_data]);
    }




    public function test(Request $request){
        $races = RaceModel::where('race_type', '=', 'race')->orderBy('position','asc')->get();
        return view('playtest',['races'=>$races]);
    }
}