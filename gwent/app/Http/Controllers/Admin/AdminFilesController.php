<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminFilesController extends BaseController
{

    protected static function getImagesInTable($table, $result_array){
        $temp = \DB::table($table)->select('img_url')->get();
        foreach ($temp as $img) {
            if($img->img_url != ''){
                $result_array[] = $img->img_url;
            }
        }
        return $result_array;
    }

    public function index(){
        $files_in_db = array();

        $files_in_db = self::getImagesInTable('tbl_race', $files_in_db); //Выборка картинок рас из БД
        $files_in_db = self::getImagesInTable('tbl_card', $files_in_db); //Выборка картинок карт из БД
        $files_in_db = self::getImagesInTable('tbl_magic_effects', $files_in_db); //Выборка картинок магических эффектов из БД

        //указываем целевой каталог с картинками (прим. дальше - дирректория)
        $dir = 'img/card_images/';

        //Массив всех файлов из дирректории
        $files_in_folder =array();

        //Если существует дирректория
         if (is_dir($dir)) {
             //Открывем соединение с дирректорией
            if ($dh = opendir($dir)) {
                //Заполняем массив файлов файлами из дирректории
                while (($file = readdir($dh)) !== false) {
                    $files_in_folder[] = $file;
                }
                //Закрываем соединение
                closedir($dh);
            }
        }

        //передаем в шаблон files все массивы
        return view('admin.files', ['files_in_db' => $files_in_db, 'files_in_folder' => $files_in_folder]);
    }

    // Удаление файлов
    public function dropFiles(Request $request){
        $data = $request -> all();

        //Принимаем массив файлов к удалению
        $files = json_decode($data['files']);
        $n=count($files);

        $dest = 'img/card_images/';

        if(is_dir($dest)){
            $dir = opendir($dest);

            while($filename = readdir($dir)){

                for($i=0; $i<$n; $i++){
                    //если имя файла входит в массив
                    if($filename == $files[$i]){
                        //удаляем
                        unlink($dest.$filename);
                    }
                }

            }

        }
        return 'success';
    }

}