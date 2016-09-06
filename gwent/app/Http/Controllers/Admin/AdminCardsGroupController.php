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

        foreach($cards as $i => $card_id){
            $card_data = \DB::table('tbl_card')->select('id','card_groups')->where('id','=',$card_id)->get();
            $card_groups = unserialize($card_data[0]->card_groups);
            $card_groups[] = $result->id;
            \DB::table('tbl_card')->where('id','=',$card_id)->update(['card_groups' => serialize($card_groups)]);
        }

        if($result !== false){
            return 'success';
        }
    }

    //Изменение группы в БД
    protected function editCardGroup(Request $request){
        $data = $request -> all();

        //Превращаем название в транслитезированую ссылку
        $slug = AdminFunctions::str2url($data['title']);

        $new_cards_in_group = array_values(array_unique(json_decode($data['cards'])));
        
        $old_cards_in_group = [];
        $temp = \DB::table('tbl_card')->select('id','card_groups')->where('card_groups','like','%'.$data['id'].'%')->get();

        foreach($temp as $card_iter => $card_data){
            $card_groups = unserialize($card_data->card_groups);
            if(in_array($data['id'], $card_groups)){
                $old_cards_in_group[] = $card_data->id;
            }
        }
        $old_cards_in_group = array_values(array_unique($old_cards_in_group));
        
        $card_array_to_drop_from_group = [];
        
        foreach($old_cards_in_group as $card_iter => $card_id){
            if(!in_array($card_id, $new_cards_in_group)){
                $card_array_to_drop_from_group[] = $card_id;
            }
        }
        
        foreach($card_array_to_drop_from_group as $card_iter => $card_id){
            $card_data = \DB::table('tbl_card')->select('id','card_groups')->where('id','=',$card_id)->get();
            $card_groups = unserialize($card_data[0]->card_groups);
            for($i=0; $i<count($card_groups); $i++){
                if($card_groups[$i] == $data['id']){
                    unset($card_groups[$i]);
                }
            }
            $card_groups = array_values(array_unique($card_groups));
            \DB::table('tbl_card')->where('id','=',$card_id)->update(['card_groups' => serialize($card_groups)]);
        }
        
        foreach($new_cards_in_group as $card_iter => $card_id){
            $card_data = \DB::table('tbl_card')->select('id','card_groups')->where('id','=',$card_id)->get();
            $card_groups = unserialize($card_data[0]->card_groups);
            $card_groups[] = $data['id'];
            $card_groups = array_values(array_unique($card_groups));
            \DB::table('tbl_card')->where('id','=',$card_id)->update(['card_groups' => serialize($card_groups)]);
        }
        
        $result = CardGroupsModel::find($data['id']);
        $result -> title = $data['title'];
        $result -> slug = $slug;
        $result -> has_cards_ids = serialize($new_cards_in_group);
        $result -> save();

        if($result != false){
            return 'success';
        }
    }


    protected function dropCardGroup(Request $request){
        $cardGroups = CardGroupsModel::find($request -> input('group_id'));
        $cards = unserialize($cardGroups -> has_cards_ids);
        foreach($cards as $i => $card_id){
            $card = CardsModel::find($card_id);

            $groups = unserialize($card->card_groups);
            foreach($groups as $j => $group){
                if($request -> input('group_id') == $group){
                    unset($groups[$j]);
                }
            }
            $groups = array_values($groups);
            $card -> card_groups = serialize($groups);
            $card -> save();
        }
        $result = $cardGroups -> delete();
        if($result !== false){
            return redirect(route('admin-cards-group'));
        }else{
            return 'Не удалось удалить группу из базы.';
        }
    }
}