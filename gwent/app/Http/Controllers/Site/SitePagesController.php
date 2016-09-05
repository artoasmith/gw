<?php
namespace App\Http\Controllers\Site;

use App\BattleModel;
use App\BattleMembersModel;
use App\CardsModel;
use App\EtcDataModel;
use App\LeagueModel;
use App\RaceModel;
use App\User;
use App\UserAdditionalDataModel;
use Crypt;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Validator;

class SitePagesController extends BaseController
{
    //Главная страница
    public function index(){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::where('race_type', '=', 'race')->orderBy('position','asc')->get();
        return view('home', ['races' => $races]);
    }

    //Страница регистрации
    public function registration(){
        $races = RaceModel::orderBy('position','asc')->get();
        return view('registration', ['races' => $races]);
    }

    //Страница доступных игр "Столы"
    public function games(Request $request){
        SiteFunctionsController::updateConnention();

        $user = Auth::user();
        //Данные пользователя
        $user_data = UserAdditionalDataModel::where('user_id', '=', $user['id'])->get();

        //Данные Лиг
        $leagues = LeagueModel::orderBy('min_lvl','asc')->get();

        //Текущие колоды пользователя
        $current_deck = unserialize($user_data[0]->user_cards_in_deck);

        if(!empty($current_deck[$request->input('currentRace')])) {
            //Вес колоды
            $deck_weight = 0;

            //Подсчет веса колоды
            foreach($current_deck[$request->input('currentRace')] as $key => $value){
                $card = CardsModel::where('id', '=', $key)->get();

                $deck_weight += $card[0]->card_value * $value;
            }
        }

        //Текущая лига
        $current_user_league = '';
        foreach ($leagues as $league) {
            //если Вес колоды больше минимального уровня вхождения в лигу
            if($deck_weight > $league['min_lvl']){
                $current_user_league = $league['title'];
            }
        }
        //Расы
        $races = RaceModel::where('race_type', '=', 'race')->orderBy('position','asc')->get();
        //Активные для данной лиги столы
        $battles = BattleModel::where('league','=',$current_user_league)->where('fight_status', '<', 3)->where('')->get();

        $battlesCount = [];
        if($battles->toArray()){

            $BattlesIDArray = [];
            foreach ($battles->toArray() as $a){
                $BattlesIDArray[] = $a['id'];
            }
            $bmm = new BattleMembersModel();
            $query = sprintf('SELECT m.`battle_id` as id, COUNT(1) as cnt FROM %s as m WHERE m.`battle_id` IN (%s) GROUP BY m.`battle_id`',$bmm->getTable(),implode(', ',$BattlesIDArray));
            if($result = \DB::select($query)){
                foreach ($result as $r){
                    $battlesCount[$r->id] = $r->cnt;
                }
            }
        }

        $user_to_update = User::find($user['id']);
        $user_to_update -> user_current_deck = $request->input('currentRace');
        $user_to_update -> save();

        return view('game', [
            'races'         => $races,
            'deck_weight'   => Crypt::encrypt($deck_weight),
            'battles'       => $battles,
            'league'        => Crypt::encrypt($current_user_league)
        ]);
    }

    //Страница боя
    public function play($id){
        SiteFunctionsController::updateConnention();

        $battle_data = BattleModel::find($id);
        if(!$battle_data){
            return view('play')->withErrors(['Данный стол не существует.']);
        }

        $sec = intval(getenv('GAME_SEC_TIMEOUT'));
        if($sec<=0) $sec = 60;

        $user = Auth::user();
        $hash = md5(getenv('SECRET_MD5_KEY').$user->id);
        return view('play', [
            'battle_data' => $battle_data,
            'hash'=>$hash,
            'user'=>$user,
            'dom'=>getenv('APP_DOMEN_NAME'),
            'timeOut'=>$sec
        ]);
    }

    //Страница "Мои карты"
    public function deck(){
        SiteFunctionsController::updateConnention();
        $deck_options = EtcDataModel::where('label_data', '=', 'deck_options')->get();
        $deck = [];
        foreach ($deck_options as $key => $value){
            $deck[$value['meta_key']] = $value['meta_value'];
        }

        $races = RaceModel::where('race_type', '=', 'race')->orderBy('position','asc')->get();
        return view('deck', ['races' => $races, 'deck' => $deck]);
    }

    //Страница "Магазин"
    public function market(){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::orderBy('position','asc')->get();
        return view('market', ['races' => $races]);
    }

    //Страница "Волшебство"
    public function marketEffects(){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::orderBy('position','asc')->get();
        return view('magic', ['races' => $races]);
    }

    //Страница "Настройки"
    public function settings(){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::orderBy('position','asc')->get();
        return view('settings', ['races' => $races]);
    }

    //Страница "Обучение"
    public function training(){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::orderBy('position','asc')->get();
        return view('training', ['races' => $races]);
    }

    //Страница WM pay
    public function WM_pay(Request $request){
        SiteFunctionsController::updateConnention();
        $races = RaceModel::orderBy('position','asc')->get();
        return view('payment.wm_pay', ['money' => $request, 'races' => $races]);
    }

    //Страцница WM success
    public function WM_success(){
        SiteFunctionsController::updateConnention();
        return view('payment.wm_success');
    }

    //Страцница WM fail
    public function WM_fail(){
        SiteFunctionsController::updateConnention();
        return view('payment.wm_fail');
    }
}
