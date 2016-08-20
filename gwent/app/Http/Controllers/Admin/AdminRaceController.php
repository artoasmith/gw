<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\RaceModel;

class AdminRaceController extends BaseController
{
    public function raceAddPage(){
        return view('admin.layout.adds.race');
    }

    public function raceEditPage($id){
        $race = RaceModel::where('id', '=', $id)->get();
        return view('admin.layout.edits.race', ['race' => $race]);
    }

    //Добавление расы
    protected function addRace(Request $request){
        $data = $request->all();

        if($data['title'] == ''){
            return 'Не указано название карты.';
        }

        if($data['slug'] == ''){
            return 'Не указано обозначение карты.';
        }

        //Если была выбрана картинка
        if('undefined' != $data['img_url']){
            //Указываем папку хранения картинок
            $destinationPath = base_path().'/public/img/card_images/';
            //Узнаем реальное имя файла
            $img_file = $data['img_url']->getClientOriginalName();
            //Создаем уникальное имя для файла
            $img_file = uniqid().'_'.$img_file;
            //СОхнаняем файл на сервере
            $data['img_url']->move($destinationPath, $img_file);
        }else{
            $img_file = '';
        }

        $result = RaceModel::create([
            'title'     => $data['title'],
            'slug'      => $data['slug'],
            'description_title' => $data['description_title'],
            'description'=>$data['description'],
            'race_type' => $data['type'],
            'img_url'   => $img_file,
            'base_card_deck'=>'a:0:{}'
        ]);

        if($result != false){
            return 'success';
        }
    }

    protected function editRace(Request $request){
        $data = $request->all();

        if($data['title'] == ''){
            return 'Не указано название карты.';
        }

        if($data['slug'] == ''){
            return 'Не указано обозначение карты.';
        }

        //Если была выбрана картинка
        if('undefined' != $data['img_url']){
            //Указываем папку хранения картинок
            $destinationPath = base_path().'/public/img/card_images/';
            //Узнаем реальное имя файла
            $img_file = $data['img_url']->getClientOriginalName();
            //Создаем уникальное имя для файла
            $img_file = uniqid().'_'.$img_file;
            //Сoхpаняем файл на сервере
            $data['img_url']->move($destinationPath, $img_file);
        }else{
            $img_file = $data['img_old_url'];
        }

        $editedRace = RaceModel::find($data['id']);
        $editedRace -> title    = $data['title'];
        $editedRace -> slug     = $data['slug'];
        $editedRace -> description_title = $data['description_title'];
        $editedRace -> description = $data['description'];
        $editedRace -> race_type= $data['type'];
        $editedRace -> img_url  = $img_file;

        $result = $editedRace -> save();
        if($result != false){
            return 'success';
        }
    }

    //Удаление расы
    protected function dropRace(Request $request){
        $dropRace = RaceModel::find($request->input('race_id'));
        $result = $dropRace -> delete();
        if($result !== false){
            return redirect(route('admin-main'));
        }
    }


    //Изменение базовой колоды карт
    protected function raceChangeDeck(Request $request){
        $data = $request -> all();

        $deck = serialize(json_decode($data['deckArray']));

        $result = RaceModel::where('slug', '=', $data['deckType'])->update(['base_card_deck' => $deck]);
        if($result != false){
            return 'success';
        }
    }
}