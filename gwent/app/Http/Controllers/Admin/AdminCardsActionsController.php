<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use App\CardActionsModel;
use App\Http\Controllers\AdminFunctions;
use Illuminate\Http\Request;

class AdminCardsActionsController extends BaseController{
    public function actionsPage(){
        //Выборка из Действий карт и отсылка в шаблон card_actions
        $card_actions = CardActionsModel::orderBy('title', 'asc')->get();
        return view('admin.card_actions', ['card_actions' => $card_actions]);
    }

    public function cardActionsAddPage(){
        //страница добавления действий карт
        return view('admin.layout.adds.card_actions');
    }

    public function cardActionsEditPage($id){
        //страница редактирования действия карты
        $action = CardActionsModel::where('id', '=', $id)->get();
        return view('admin.layout.edits.card_actions', ['action' => $action]);
    }


    protected function addCardAction(Request $request){
        $data = $request->all();

        //функция транслитеризации см. app\http\AdminFunctions.php
        $slug = AdminFunctions::str2url($data['title']);

        //массив характеристик действий карты
        $charac = array();

        /*
        * если существует входящий массив характеристик приводим его к виду:
        * array(
        *  [порядковый номер характеристики]=>[описание][html]
        * )
        */
        if(isset($data['characteristics'])){
            $n = count($data['characteristics']);

            for($i=0; $i<$n; $i++){
                if($i%2 == 1){
                    $charac[] = array($data['characteristics'][$i-1], $data['characteristics'][$i]);
                }
            }
        }

        //превращаем массив в строку
        $charac = serialize($charac);

        //заносим в БД
        $result = CardActionsModel::create([
            'title'         => $data['title'],
            'slug'          => $slug,
            'description'   => $data['description'],
            'html_options'  => $charac
        ]);

        //если действие занесено в БД, передаем в AJAX запрос success
        if($result !== false){
            return 'success';
        }
    }

    protected function editCardAction(Request $request){
        $data = $request->all();

        //Находим в БД редактируемое действие карты
        $editedCardAction = CardActionsModel::find($data['id']);
        if(!empty($editedCardAction)){

            //функция транслитеризации см. app\http\AdminFunctions.php
            $slug = AdminFunctions::str2url($data['title']);

            //массив характеристик действий карты
            $charac = array();

            /*
            * если существует входящий массив характеристик приводим его к виду:
            * array(
            *  [порядковый номер характеристики]=>[описание][html]
            * )
            */
            if(isset($data['characteristics'])){
                $n = count($data['characteristics']);
                for($i=0; $i<$n; $i++){
                    if($i%2 == 1){
                        $charac[] = array($data['characteristics'][$i-1], $data['characteristics'][$i]);
                    }
                }
            }

            $charac = serialize($charac);

            //Изменение данных
            $editedCardAction->title        = $data['title'];
            $editedCardAction->slug         = $slug;
            $editedCardAction->description  = $data['description'];
            $editedCardAction->html_options = $charac;

            //Сохранение в БД
            $result = $editedCardAction->save();
            if($result !== false){
                return 'success';
            }
        }
    }

    //Удаление действий карт
    protected function dropCardAction(Request $request){
        $dropCardAction = CardActionsModel::find($request->input('adm_id'));
        $result = $dropCardAction -> delete();
        if($result !== false){
            return redirect(route('admin-cards-actions'));
        }
    }
}