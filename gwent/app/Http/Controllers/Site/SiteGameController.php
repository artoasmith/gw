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

    public static function updateBattleMembers($user_id, $battle_id, $user_deck, $user_magic, $user_energy){
        $user_is_battle_member = BattleMembersModel::where('user_id', '=', $user_id)->get();

        if( 1 > count($user_is_battle_member) ){
            $result = BattleMembersModel::create([
                'user_id'       => $user_id,
                'battle_id'     => $battle_id,
                'user_deck'     => $user_deck,
                'magic_effects' => $user_magic,
                'user_energy'   => $user_energy
            ]);
        }else{
            $user_battle = BattleMembersModel::find($user_is_battle_member[0]->id);
            $user_battle -> battle_id       = $battle_id;
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
                serialize($user_settings['deck']),
                serialize($user_settings['magic_effects']),
                $user_data[0]->user_energy
            );

            dd($battle_members);
            /*
            $user_is_battle_member = BattleMembersModel::where('user_id', '=', $user['id'])->get();

            if( 1 > count($user_is_battle_member) ){
                BattleMembersModel::create([
                    'user_id'       => $user['id'],
                    'battle_id'     => $result -> id,
                    'user_deck'     => serialize($user_deck),
                    'magic_effects' => serialize($user_magic),
                    'user_energy'   => $user_data[0]->user_energy
                ]);
            }else{
                $user_battle = BattleMembersModel::find($user_is_battle_member[0]->id);
                $user_battle -> battle_id       = $result -> id;
                $user_battle -> user_deck       = serialize($user_deck);
                $user_battle -> magic_effects   = serialize($user_magic);
                $user_battle -> user_energy     = $user_data[0]->user_energy;
                $user_battle -> save();
            }

            $battle_log_result = BattleLogModel::create([
                'battle_id' => $result -> id,
                'fight_log' => '<p>Стол № '.$result -> id.' создан плльзователем '.$user['login'].'(ID = '.$user['id'].') </p>'
            ]);

            if($battle_log_result !== false){
                return redirect(route('user-in-game', ['game' => $result->id]));
            }*/
        }
    }

    protected function userConnectToBattle(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        $user = Auth::user();
        $battle_data = BattleModel::find($data['id']);

        if($battle_data->creator_id == $user['id']){
            return json_encode(['message' => 'own_create']);
        }

        //Если стол не пользовательский

    }






    public function test(Request $request){
        $races = RaceModel::where('race_type', '=', 'race')->orderBy('position','asc')->get();
        return view('playtest',['races'=>$races]);
    }
}