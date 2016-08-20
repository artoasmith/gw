<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\AdminViews;
use App\RaceModel;
use App\LeagueModel;
use App\EtcDataModel;
use Illuminate\Http\Request;

class AdminIndexController extends BaseController
{
    public function index(){
        //Выбираем все расы из БД
        $races = RaceModel::orderBy('title','asc')->get();
        //Выбираем все лиги из БД
        $leagues = LeagueModel::orderBy('title', 'asc')->get();
        return view('admin.main', ['races' => $races, 'leagues' => $leagues]);
    }

    //Таблица базовых карт - создание строки
    public function getAllCardsSelector(){
        return '
            <tr>
                <td><a href="#" class="drop"></a></td>
                <td>'.AdminViews::getAllCardsSelectorView().'</td>
                <td><input name="currentQuantity" type="number" value=""></td>
            </tr>
        ';
    }

    //Сохранение в БД данных о лигах
    protected function leagueEdit(Request $request){
        $data = $request -> all();

        //Входящий массив лиг
        $league_data = json_decode($data['leagueData']);

        //Удаляем все лиги из БД
        $leagueModel = LeagueModel::get();
        foreach($leagueModel as $league) {
            $league->delete();
        }

        //Создаем новые лиги из входящего массива
        foreach($league_data as $league) {
            $result = LeagueModel::create([
                'title'     => $league -> title,
                'min_lvl'   => $league -> min,
                'max_lvl'   => $league -> max
            ]);
        }
        if($result != false){
            return 'success';
        }
    }

    //Сохранение в БД базовых полей пользователя
    protected function baseUserFieldsEdit(Request $request){
        $data = $request -> all();

        $result = array();
        //Для каждого входящего элемента обновляем значение в БД
        foreach($data as $key => $value){
            if( ($key != '_token') and ($key != '_method') ){
                $result[] = EtcDataModel::where('label_data', '=', 'base_user_fields')->where('meta_key', '=', $key)->update(['meta_value' => $value]);
            }
        }

        if(!in_array(false, $result, true)){
            return redirect(route('admin-main'));
        }
    }


    //Сохранение соотношения обменов
    protected function exchangeOptionsEdit(Request $request){
        $data = $request->all();

        $result = array();
        //Для каждого входящего элемента обновляем значение в БД
        foreach($data as $key => $value) {
            if (($key != '_token') and ($key != '_method')) {
                $result[] = EtcDataModel::where('label_data', '=', 'exchange_options')->where('meta_key', '=', $key)->update(['meta_value' => $value]);
            }
        }

        if(!in_array(false, $result, true)){
            return redirect(route('admin-main'));
        }
    }

    //Сохранение настроек колоды
    protected function deckOptionsEdit(Request $request){
        $data = $request -> all();

        $result = array();
        //Для каждого входящего элемента обновляем значение в БД
        foreach($data as $key => $value) {
            if (($key != '_token') and ($key != '_method')) {
                $result[] = EtcDataModel::where('label_data', '=', 'deck_options')->where('meta_key', '=', $key)->update(['meta_value' => $value]);
            }
        }

        if(!in_array(false, $result, true)){
            return redirect(route('admin-main'));
        }
    }
}