<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\CardGroupsModel;
use App\CardsModel;
use App\Http\Controllers\AdminFunctions;
use Illuminate\Http\Request;

class AdminCardsGroupController extends BaseController
{
    public function groupPage(){
        //Выборка из групп карт и отсылка в шаблон card_groups
        $card_groups = CardGroupsModel::orderBy('title', 'asc')->get();
        return view('admin.card_groups', ['card_groups' => $card_groups]);
    }

    public function cardGroupAddPage(){
        //Станица добавления группы карты
        //Выбираем все карты из БД и передаем в шаблон adds/card_groups
        $cards = CardsModel::orderBy('title', 'asc')->get();
        return view('admin.layout.adds.card_groups', ['cards' => $cards]);
    }

    public function cardGroupEditPage($id){
        //Страница редактирования группы карт
        //Находим в БД текущую группу
        $group = CardGroupsModel::where('id', '=', $id)->get();
        //Выбираем все карты из БД и передаем в шаблон edits/card_groups
        $cards = CardsModel::orderBy('title', 'asc')->get();
        return view('admin.layout.edits.card_groups', ['group' => $group, 'cards' => $cards]);
    }

    //Добавление группы в БД
    protected function addCardGroup(Request $request){
        if( csrf_token() == $request -> input('token') ){
            $data = $request -> all();

            //Превращаем название в транслитезированую ссылку
            $slug = AdminFunctions::str2url($data['title']);

            //Массив карт входящих в группу
            $cards = array_values(array_unique(json_decode($data['cards'])));

            //Создание в БД новой группы
            $result = CardGroupsModel::create([
                'title' => $data['title'],
                'slug'  => $slug,
                'has_cards_ids' => serialize($cards)
            ]);

            if($result !== false){
                return 'success';
            }
        }
    }

    //Изменение группы в БД
    protected function editCardGroup(Request $request){
        if( csrf_token() == $request -> input('token') ){
            $data = $request -> all();

            //Превращаем название в транслитезированую ссылку
            $slug = AdminFunctions::str2url($data['title']);

            //Массив карт входящих в группу
            $cards = array_values(array_unique(json_decode($data['cards'])));

            //Изменение группы
            $group_data = CardGroupsModel::find($data['id']);
            $group_data -> title            = $data['title'];
            $group_data -> slug             = $slug;
            $group_data -> has_cards_ids    = serialize($cards);

            $result = $group_data -> save();

            if($result != false){
                return 'success';
            }
        }
    }


    protected function dropCardGroup(Request $request){
        if(csrf_token() == $request->input('_token')){
            $cardGroups = CardGroupsModel::find($request -> input('group_id'));
            $result = $cardGroups -> delete();
            if($result !== false){
                return redirect(route('admin-cards-group'));
            }else{
                return 'Не удалось удалить группу из базы.';
            }
        }
    }
}