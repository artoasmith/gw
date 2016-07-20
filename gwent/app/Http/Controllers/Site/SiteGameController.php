<?php
namespace App\Http\Controllers\Site;

use App\BattleModel;
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
    protected function createTable(Request $request){
        SiteFunctionsController::updateConnention();
        $data = $request->all();

        if (($data['players'] % 2 == 0) && ($data['players'] <= 8) && ($data['players'] >= 2)) {
            $user = Auth::user();

            $deck_weight = substr(SiteFunctionsController::dsCrypt(base64_decode($data['deck_weight']), 1), 3);
            $league = substr(SiteFunctionsController::dsCrypt(base64_decode($data['league']), 1), 3);

            $players_deck[] = [$user['id'] => $user['user_current_deck']];

            /*$result = BattleModel::create([
                'creator_id'        => $user['id'],
                'players_decks'     => serialize($players_deck),
                'players_quantity'  => $data['players'],
                'deck_weight'       => $deck_weight,
                'league'            => $league,
                'fight_status'      => 0,
                'player_num_turn'   => rand(0, $data['players']-1),
                'round_status'      => serialize(['enemy'=>[], 'alias'=>[]]),
                'fight_log'         => '<p>'.$user['title'].' создал стол</p>'
            ]);

            if($result !== false){
                return json_encode(['game' => SiteFunctionsController::dsCrypt(base64_encode('000'.$result->id)), 'message' => 'success']);
            }*/
        }
    }
}