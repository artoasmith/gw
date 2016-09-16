<?php

namespace App\Http\Controllers\Admin;

use App\RaceModel;
use Illuminate\Routing\Controller as BaseController;
use App\CardGroupsModel;
use App\CardActionsModel;
use App\CardsModel;
use App\Http\Controllers\AdminFunctions;
use Illuminate\Http\Request;

class AdminCardsController extends BaseController
{
    //Вывод списка карт
    public function index(Request $request){
        if( !empty($request -> all()) ){
            if(isset($request -> all()['race'])){
                $race_slug = $request -> all()['race'];
            }else{
                $race_slug = 'knight';
            }
        }else{
            $race_slug = 'knight';
        }
        if( ( $race_slug == 'special') || ($race_slug == 'neutrall') ){
            $field = 'card_type';
        }else{
            $field = 'card_race';
        }

        $races = RaceModel::orderBy('position', 'asc')->get();
        $cards = CardsModel::where($field,'=', $race_slug)->orderBy('title','asc')->orderBy('price_gold','asc')->orderBy('price_silver','asc')->get();
        return view('admin.cards', ['cards' => $cards, 'races' => $races, 'race_slug' => $race_slug]);
    }

    //Страница добавления карты. Принимает список Действий Карт
    public function cardAddPage(){
        $card_actions = CardActionsModel::orderBy('title', 'asc')->get();
        return view('admin.layout.adds.cards', ['card_actions' => $card_actions]);
    }

    //Страница редактирования карты. Принимает $card -> данные карты; $card_actions -> список Действий Карт
    public function cardEditPage($id){
        $card_actions = CardActionsModel::orderBy('title', 'asc')->get();   //Выборка из БД действий
        $card = CardsModel::where('id', '=', $id)->get();                   //Выборка из БД карты
        return view('admin.layout.edits.cards', ['card' => $card, 'card_actions' => $card_actions]);
    }

    //Добавление карты
    protected function addCard(Request $request){
        $data = $request->all();

        if($data['title'] == ''){
             return 'Не указано название карты.';
        }
        $slug = AdminFunctions::str2url($data['title']);

        //Если была выбрана картинка
        if('undefined' != $data['img_url']){
            //Указываем папку хранения картинок
            $destinationPath = base_path().'/public/img/card_images/';
            //Узнаем реальное имя файла
            $img_file = $data['img_url']->getClientOriginalName();
            //Создаем уникальное имя для файла
            $img_file = uniqid().'_'.AdminFunctions::str2url($img_file);
            //СОхнаняем файл на сервере
            $data['img_url']->move($destinationPath, $img_file);
        }else{
            $img_file  = '';
        }

        $card_actions = serialize(json_decode($data['card_actions'])); //Массив Действий карты

        //Если карта "специальная" - создаем список рас которым запрещено пользоваться данной картой
        $card_type_forbidden_race_deck = serialize(json_decode($data['card_type_forbidden_race_deck']));

        //Если карта "рассовая" - указываем к какой расе она принадлежит
        if('race' == $data['card_type']){
            $card_race = $data['card_race'];
        }else{
            $card_race = '';
        }

        //Карта действует на ряд
        $card_action_row = serialize(json_decode($data['card_action_row']));

        $card_refer_to_group = serialize(json_decode($data['card_refer_to_group']));

        //Карта-лидер
        if($data['card_is_leader'] == 'false'){
            $card_is_leader = 0;
        }else{
            $card_is_leader = 1;
        }

        //Заносим карту в БД
        $result = CardsModel::create([
            'title'             => $data['title'],
            'slug'              => $slug,
            'card_type'         => $data['card_type'],
            'card_race'         => $card_race,
            'forbidden_races'   => $card_type_forbidden_race_deck,
            'allowed_rows'      => $card_action_row,
            'card_strong'       => $data['card_strenght'],
            'card_value'        => $data['card_weight'],
            'is_leader'         => $card_is_leader,
            'img_url'           => $img_file,
            'card_actions'      => $card_actions,
            'card_groups'       => $card_refer_to_group,
            'max_quant_in_deck' => $data['card_max_num_in_deck'],
            'short_description' => $data['short_descr'],
            'full_description'  => $data['full_descr'],
            'price_gold'        => $data['card_gold_price'],
            'price_silver'      => $data['card_silver_price'],
            'price_only_gold'   => $data['card_only_gold_price']
        ]);

        //Если карта успешно записана в БД
        if($result !== false){
            //если существует группа к которой карта отнесена
            $card_refer_to_group = json_decode($data['card_refer_to_group']);
            if(!empty($card_refer_to_group)){
                foreach($card_refer_to_group as $group){
                    /*
                     * Где-то здесь у меня кончилось красноречие
                     * В общем мы ищем каждую группу в БД, находим к которой относится наша карта
                     * Дописываем Id карты в конец массива карт дайнной группы
                     * Оставляем этом массиве уникальные значения
                    */
                    $card_group = CardGroupsModel::find($group);

                    $has_cards_ids = unserialize($card_group->has_cards_ids);
                    $has_cards_ids[] = $result->id;
                    $has_cards_ids = serialize(array_values(array_unique($has_cards_ids)));

                    $card_group->has_cards_ids = $has_cards_ids;
                    $card_group->save();
                }
            }
            return 'success';
        }else{
            return 'Не удалось записать карту в базу.';
        }
    }


    protected function editCard(Request $request){
        /*Здесь все по аналогии с добавлением карты*/
        $data = $request->all();
        if ($data['title'] == '') {
            return 'Не указано название карты.';
        }
        $slug = AdminFunctions::str2url($data['title']);

        if ('undefined' != $data['img_url']) {
            $destinationPath = base_path() . '/public/img/card_images/';
            $img_file = $data['img_url']->getClientOriginalName();
            $img_file = uniqid() . '_' .AdminFunctions::str2url($img_file);
            $data['img_url']->move($destinationPath, $img_file);
        } else {
            //Если новая картинка не указана, используем старую
            $img_file = $data['img_old_url'];
        }

        $card_actions = serialize(json_decode($data['card_actions']));

        $card_type_forbidden_race_deck = serialize(json_decode($data['card_type_forbidden_race_deck']));

        if('race' == $data['card_type']){
            $card_race = $data['card_race'];
        }else{
            $card_race = '';
        }

        $card_action_row = serialize(json_decode($data['card_action_row']));

        $card_refer_to_group = serialize(json_decode($data['card_refer_to_group']));

        if ($data['card_is_leader'] == 'false') {
            $card_is_leader = 0;
        } else {
            $card_is_leader = 1;
        }
       
        //Находим в БД редактируемую карту
        $editedCard = CardsModel::find($data['id']);
        $editedCard->title              = $data['title'];
        $editedCard->slug               = $slug;
        $editedCard->card_type          = $data['card_type'];
        $editedCard->card_race          = $card_race;
        $editedCard->forbidden_races    = $card_type_forbidden_race_deck;
        $editedCard->allowed_rows       = $card_action_row;
        $editedCard->card_strong        = $data['card_strenght'];
        $editedCard->card_value         = $data['card_weight'];
        $editedCard->is_leader          = $card_is_leader;
        $editedCard->img_url            = $img_file;
        $editedCard->card_actions       = $card_actions;
        $editedCard->card_groups        = $card_refer_to_group;
        $editedCard->max_quant_in_deck  = $data['card_max_num_in_deck'];
        $editedCard->short_description  = $data['short_descr'];
        $editedCard->full_description   = $data['full_descr'];
        $editedCard->price_gold         = $data['card_gold_price'];
        $editedCard->price_silver       = $data['card_silver_price'];
        $editedCard->price_only_gold    = $data['card_only_gold_price'];

        $result = $editedCard -> save();

        if($result != false){
            $card_refer_to_group = json_decode($data['card_refer_to_group']);
            if(!empty($card_refer_to_group)){
                foreach($card_refer_to_group as $group){
                    $card_group = CardGroupsModel::find($group);

                    $has_cards_ids = unserialize($card_group->has_cards_ids);
                    $has_cards_ids[] = $data['id'];
                    $has_cards_ids = serialize(array_values(array_unique($has_cards_ids)));

                    $card_group->has_cards_ids = $has_cards_ids;
                    $card_group->save();
                }
            }
            return 'success';
        }
    }



    //Удаление карты
    protected function dropCard(Request $request){
        //Находим по id
        $dropCard = CardsModel::find($request->input('card_id'));
        //Удаляем
        $result = $dropCard -> delete();

        $cardGroups = CardGroupsModel::get();
        //Удалем из группы
        foreach($cardGroups as $groups){
            $has_cards_ids = unserialize($groups->has_cards_ids);
            unset($has_cards_ids[array_search($request->input('card_id'), $has_cards_ids)]);
            $has_cards_ids = serialize(array_values(array_unique($has_cards_ids)));
            $groups -> has_cards_ids = $has_cards_ids;
            $groups -> save();
        }
        if($result !== false){
            return redirect(route('admin-cards'));
        }else{
            return 'Не удалось удалить карту из базу.';
        }
    }

}