<?php
namespace App\Http\Controllers\Site;

use App\EtcDataModel;
use App\RaceModel;
use App\MagicEffectsModel;
use Validator;
use App\User;
use App\UserAdditionalDataModel;
use Auth;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Http\Request;

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
