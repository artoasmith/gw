<?php

namespace App\Http\Controllers\Admin;

use App\MagicEffectsModel;
use App\RaceModel;
use App\Http\Controllers\AdminFunctions;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminMagicEffectsController extends BaseController
{
    //Старница вывода всех магических еффектов (волшебства)
    public function magicEffectsPage(){
        //Выборка из БД магических еффектов
        $effects = MagicEffectsModel::orderBy('race','asc')->orderBy('title','asc')->get();
        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        return view('admin.magic', ['effects'=> $effects, 'races' => $races]);
    }

    //Страница добавления волшебства
    public function magicEffectsAddPage(){
        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        return view('admin.layout.adds.magic', ['races' => $races]);
    }

    //Страница редактирования магических еффектов
    public function magicEffectsEditPage($id){
        //Выборка из БД текущего магического еффекта
        $effect = MagicEffectsModel::where('id', '=', $id)->get();
        //Выборка из БД доступных рас
        $races = RaceModel::orderBy('title','asc')->get();
        return view('admin.layout.edits.magic', ['effect'=> $effect->all(), 'races' => $races]);
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

            $races = serialize(json_decode($data['races']));

            $result = MagicEffectsModel::create([
                'title'         => $data['title'],
                'slug'          => $slug,
                'img_url'       => $img_file,
                'description'   => $data['description'],
                'energy_cost'   => $data['energyCost'],
                'price_gold'    => $data['price_gold'],
                'price_silver'  => $data['price_silver'],
                'effect_actions'=> 'a:0:{}',
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

            if ($data['title'] == '') {
                return 'Не указано название карты.';
            }

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
            $currentMagicEffect->race           = $races;

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
}