<?php

namespace App\Http\Controllers\Admin;

use App\MagicEffectsModel;
use App\MagicActionsModel;
use App\RaceModel;
use App\Http\Controllers\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminMagicEffectsController extends BaseController
{
    //Старница вывода всех магических еффектов (волшебства)
    public function magicEffectsPage(){
        //Выборка из БД магических еффектов
        $effects = MagicEffectsModel::orderBy('race','asc')->orderBy('energy_cost','asc')->get();

        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        return view('admin.magic', ['effects'=> $effects, 'races' => $races]);
    }


    //Страница добавления волшебства
    public function magicEffectsAddPage(){
        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        $magic_actions = MagicActionsModel::orderBy('title', 'asc')->get();
        return view('admin.layout.adds.magic', ['races' => $races, 'magic_actions' => $magic_actions]);
    }

    //Страница редактирования магических еффектов
    public function magicEffectsEditPage($id){
        //Выборка из БД текущего магического еффекта
        $effect = MagicEffectsModel::where('id', '=', $id)->get();
        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        $magic_actions = MagicActionsModel::orderBy('title', 'asc')->get();
        return view('admin.layout.edits.magic', ['effect'=> $effect->all(), 'races' => $races, 'magic_actions' => $magic_actions]);
    }

    //Добавление в БД магического еффекта
    protected function addMagicEffects(Request $request){
        if( csrf_token() == $request->input('token')){
            $data = $request->all();

            if($data['title'] == ''){
                return 'Не указано название карты.';
            }
            //создание ссылки для магического еффекта
            $slug = AdminFunctions::str2url($data['title']);

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

            $magic_actions = serialize(json_decode($data['magic_actions'])); //Массив Действий волшебства

            $races = serialize(json_decode($data['races']));

            $result = MagicEffectsModel::create([
                'title'         => $data['title'],
                'slug'          => $slug,
                'img_url'       => $img_file,
                'description'   => $data['description'],
                'energy_cost'   => $data['energyCost'],
                'price_gold'    => $data['price_gold'],
                'price_silver'  => $data['price_silver'],
                'usage_count'   => $data['usage_count'],
                'effect_actions'=> $magic_actions,
                'race'          => $races
            ]);

            if($result !== false){
                return 'success';
            }
        }
    }

    //Изменение магического еффекта
    protected function editMagicEffects(Request $request){
        if (csrf_token() == $request->input('token')) {
            $data = $request -> all();

            //создание ссылки для магического еффекта
            $slug = AdminFunctions::str2url($data['title']);

            if ('undefined' != $data['img_url']) {
                //Указываем папку хранения картинок
                $destinationPath = base_path() . '/public/img/card_images/';
                //Узнаем реальное имя файла
                $img_file = $data['img_url']->getClientOriginalName();
                //Создаем уникальное имя для файла
                $img_file = uniqid() . '_' . AdminFunctions::str2url($img_file);
                //СОхнаняем файл на сервере
                $data['img_url']->move($destinationPath, $img_file);
            } else {
                $img_file = $data['img_old_url'];
            }

            $races = serialize(json_decode($data['races']));

            $magic_actions = serialize(json_decode($data['magic_actions'])); //Массив Действий волшебства

            //Находим в БД текущий магический еффект
            $currentMagicEffect = MagicEffectsModel::find($data['id']);
            //Вносим изменения
            $currentMagicEffect->title          = $data['title'];
            $currentMagicEffect->slug           = $slug;
            $currentMagicEffect->img_url        = $img_file;
            $currentMagicEffect->description    = $data['description'];
            $currentMagicEffect->energy_cost    = $data['energyCost'];
            $currentMagicEffect->price_gold     = $data['price_gold'];
            $currentMagicEffect->price_silver   = $data['price_silver'];
            $currentMagicEffect->usage_count    = $data['usage_count'];
            $currentMagicEffect->race           = $races;
            $currentMagicEffect->effect_actions = $magic_actions;

            //Применяем изменения
            $result = $currentMagicEffect -> save();

            if($result !== false){
                return 'success';
            }
        }
    }


    protected function dropMagicEffect(Request $request){
        if(csrf_token() == $request->input('_token')){
            $dropMagicEffect = MagicEffectsModel::find($request->input('effect_id'));
            $result = $dropMagicEffect -> delete();
            if($result !== false){
                return redirect(route('admin-magic-effects'));
            }
        }
    }






    public function magicActionsPage(){
        //Выборка из Действий Магии и отсылка в шаблон magic_actions
        $magic_actions = MagicActionsModel::orderBy('title', 'asc')->get();
        return view('admin.magic_action', ['magic_actions' => $magic_actions]);
    }


    public function magicActionsAddPage(){
        //страница добавления действий карт
        return view('admin.layout.adds.magic_actions');
    }


    public function magicActionsEditPage($id){
        //страница редактирования действия карты
        $magic_actions = MagicActionsModel::where('id', '=', $id)->get();
        return view('admin.layout.edits.magic_actions', ['magic_actions' => $magic_actions]);
    }


    protected function addMagicAction(Request $request){
        //Проверка на кроссайтовую передачу данных
        if(csrf_token() == $request->input('token')){

            $data = $request->all();

            //функция транслитеризации см. app\http\AdminFunctions.php
            $slug = AdminFunctions::str2url($data['title']);

            //массив характеристик действий волшебства
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
            $result = MagicActionsModel::create([
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
    }


    protected function editMagicAction(Request $request){
        //Проверка на кроссайтовую передачу данных
        if(csrf_token() == $request->input('token')){

            $data = $request->all();

            //Находим в БД редактируемое действие волшебства
            $editedMagicAction = MagicActionsModel::find($data['id']);
            if(!empty($editedMagicAction)){

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
                $editedMagicAction->title        = $data['title'];
                $editedMagicAction->slug         = $slug;
                $editedMagicAction->description  = $data['description'];
                $editedMagicAction->html_options = $charac;

                //Сохранение в БД
                $result = $editedMagicAction->save();
                if($result !== false){
                    return 'success';
                }
            }

        }
    }


    //Удаление действий волшебства
    protected function dropMagicAction(Request $request){
        if(csrf_token() == $request->input('_token')){
            $dropMagicAction = MagicActionsModel::find($request->input('adm_id'));
            $result = $dropMagicAction -> delete();
            if($result !== false){
                return redirect(route('admin-magic-actions'));
            }
        }
    }
}