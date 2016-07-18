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
    protected function userWantToPlay(Request $request){
        $user = Auth::user();

        $user_data = UserAdditionalDataModel::where('user_id','=',$user['id'])->get();

        $current_deck = unserialize($user_data[0]->user_cards_in_deck);

        $deck_options = EtcDataModel::where('label_data', '=', 'deck_options')->get();
        $deck_rules = [];
        foreach($deck_options as $key => $value){
            $deck_rules[$value->meta_key] = $value->meta_value;
        }

        $leagues = LeagueModel::orderBy('min_lvl','asc')->get();

        //Валидация колоды

        //Если колода не пуста
        if(!empty($current_deck[$request->input('race')])){

            $error = '';

            $deck_weight = 0;

            $leader_card_quantity = 0;
            $warrior_card_quantity = 0;
            $special_card_quantity = 0;
            foreach($current_deck[$request->input('race')] as $key => $value){
                $card = CardsModel::where('id', '=', $key)->get();
                //Проверяем максимальное колличество карт каждого типа
                if($value > $card[0]->max_quant_in_deck){
                    $error .= '<p>В колоде находится слишком много карт "'.$card[0]->title.'" (Максимальное колличество - '.$card[0]->max_quant_in_deck.').</p>';
                }

                //Узнаем "Вес" колоды
                $deck_weight += $card[0]->card_value;

                //Количество карт-лидеров
                if(0 != $card[0]->is_leader){
                    $leader_card_quantity++;
                }

                //Количество спец. карт
                if($card[0]->card_type == 'special'){
                    $special_card_quantity++;
                }else{
                    //Количество карт-воинов
                    $warrior_card_quantity++;
                }
            }

            if( ($warrior_card_quantity + $special_card_quantity) > $deck_rules['maxCardQuantity']){
                $error .= '<p>Количество карт в колоде должно быть не больше '.$deck_rules['maxCardQuantity'].' штук</p>';
            }

            if($warrior_card_quantity < $deck_rules['minWarriorQuantity']) {
                $error .= '<p>Количество карт воинов в  колоде должно быть не меньше '.$deck_rules['minWarriorQuantity'].' штук</p>';
            }

            if($special_card_quantity > $deck_rules['specialQuantity']){
                $error .= '<p>Количество спец. карт в колоде должно быть не больше '.$deck_rules['specialQuantity'].' штук</p>';
            }

            if($leader_card_quantity > $deck_rules['leaderQuantity']){
                $error .= '<p>Количество карт лидеров в колоде должно быть не больше '.$deck_rules['leaderQuantity'].' штук</p>';
            }

            //Если есть ошибки валидации
            if($error != ''){
                return json_encode(['message' => $error]);
            }

            //Узнаем лигу текущей колоды
            $current_user_league = '';
            foreach ($leagues as $league) {
                //если Вес колоды больше минимального уровня вхождения в лигу
                if($deck_weight > $league['min_lvl']){
                    $current_user_league = $league['title'];
                }
            }

            $available_battles = BattleModel::where('league', '=', $current_user_league)->get();

            if(count($available_battles) < 1){

            }



        }else{
            return json_encode(['message' => 'Пустая колда']);
        }
    }
}