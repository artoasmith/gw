<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class AdminFunctions extends BaseController
{
    
    public static function rus2translit($string) {
        //Массив трансформации букв
        $converter = array(
                'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e',
                'ё'=>'e', 'ж'=>'zh', 'з'=>'z', 'и'=>'i', 'й'=>'j', 'к'=>'k',
                'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
                'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts',
                'ч'=>'ch', 'ш'=>'sh', 'щ'=>'shch', 'ь'=>'', 'ы'=>'y', 'ъ'=>'',
                'э'=>'e', 'ю'=>'yu', 'я'=>'ya', 'і'=>'i', 'ї'=>'i', 'є'=>'ie',
                'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E',
                'Ё'=>'E', 'Ж'=>'Zh', 'З'=>'Z', 'И'=>'I', 'Й'=> 'J', 'К'=>'K',
                'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
                'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц' => 'Ts',
                'Ч'=>'Ch', 'Ш'=>'Sh', 'Щ'=>'Shch', 'Ь'=>'', 'Ы'=>'Y', 'Ъ'=>'',
                'Э'=>'E', 'Ю'=>'Yu', 'Я'=>'Ya', 'І'=>'I', 'Ї'=>'I', 'Є'=>'Ie');
            //замена кирилицы входящей строки на латынь
        return strtr($string, $converter);
    }
    
    public static function str2url($str){
        $str = self::rus2translit($str);
        $str = strtolower($str);
        $str = preg_replace('~[^-a-z0-9_\.]+~u', '_', $str);
        $str = trim($str, "_");
        return $str;
    }

}

