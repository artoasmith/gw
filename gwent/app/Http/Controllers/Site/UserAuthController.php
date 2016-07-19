<?php
namespace App\Http\Controllers\Site;

use App\EtcDataModel;
use App\RaceModel;
use Validator;
use App\User;
use App\UserAdditionalDataModel;
use App\Http\Controllers\AdminFunctions;
use Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class UserAuthController extends BaseController
{
    public function login(Request $request){
        $data = $request->all();

        $login = htmlspecialchars(strip_tags(trim($data['login'])));
        $password = htmlspecialchars(strip_tags(trim($data['password'])));
        $password = md5($password.$login);
        $user_isset = User::where('login', '=', $login )->where('password', '=', $password)->count();

        if(1 == $user_isset){
            $user = User::where('login', '=', $login )->where('password', '=', $password)->get();
            $auth = Auth::loginUsingId($user[0]->id);
            if(!$auth){
                return redirect(route('user-home'))->withErrors(['Ошибка авторизации']);
            }else{
                User::where('login', '=', $login )->where('password', '=', $password)->update(['user_online' => 1]);
                return redirect(route('user-home'));
            }
        }else{
            return redirect(route('user-home'))->withErrors(['Не правильный логин или пароль.']);
        }

    }

    public function logout(){
        $user = Auth::user();
        User::where('login', '=', $user['login'] )->where('password', '=', $user['password'])->update(['user_online' => 0]);
        Auth::logout();
        return redirect('/');
    }

    protected function userRegistration(Request $request){

        $data = $request -> all();

        //Валидация основных данных пользователя
        $validator = Validator::make($data, [
            'login'     => 'required|max:255|min:6',
            'password'  => 'required|min:6',
            'email'     => 'required|email'
        ]);
        /*
         * Если данные не прошли валидацию, отправляем отчет
         * о ошибках на /registration
        */
        if($validator->fails()){
            return redirect(route('user-registration'))->withErrors($validator);
        }

        $login = htmlspecialchars(strip_tags(trim($data['login'])));
        $email = htmlspecialchars(strip_tags(trim($data['email'])));

        $password = htmlspecialchars(strip_tags(trim($data['password'])));
        $conf_pass = htmlspecialchars(strip_tags(trim($data['confirm_password'])));

        //если праоль не сходится с подтверждением
        if($password != $conf_pass){
            return redirect(route('user-registration'))->withErrors(['Подтвердите пароль']);
        }


        $password = md5($password.$login);

        $user_race = htmlspecialchars(strip_tags(trim($data['fraction_select'])));

        //Узнаем есть ли в БД пользователь с таким же логином
        $user = User::where('login', '=', $login )->where('password', '=', $password)->count();

        //Если такого пользователя не существует
        if(0 == $user){
            //Создаем пользователя и заносим его в таблицу с основными данными users
            $result = User::create([
                'login'     => $login,
                'email'     => $email,
                'password'  => $password,
                'is_banned' => '0',
                'ban_time'  => '0000-00-00 00:00:00',
                'user_role' => '0',
                'user_online' => '1'
            ]);

            //Если пользователь создан успешно
            if($result !== false){
                //Проводим логинизацию
                $auth = Auth::loginUsingId($result->id);

                //Базовые значения ресурсов пользователя
                $base_fields = EtcDataModel::where('label_data', '=', 'base_user_fields')->get();
                $user_begin_data = [];
                foreach($base_fields as $key){
                    $user_begin_data[$key->meta_key] = $key->meta_value;
                }

                //Начальная раса пользователя
                $races = RaceModel::where('race_type', '=', 'race')->get();
                $user_card_deck = [];
                foreach($races as $race){
                    $user_card_deck[$race->slug] = [];
                }
                $races = RaceModel::where('slug', '=', $user_race)->get();

                //Массив начальных доступных карт
                $available_deck = [];
                $race_deck = unserialize($races[0]->base_card_deck);
                foreach($race_deck as $key => $value){
                    $available_deck[$value->id] = $value -> q;
                }

                //Вносим в tbl_user_data дополнительные данные пользователя
                $result = UserAdditionalDataModel::create([
                    'user_id'           => $result->id,
                    'login'             => $login,
                    'email'             => $email,
                    'user_base_race'    => $data['fraction_select'],
                    'user_gold'         => $user_begin_data['baseGold'],
                    'user_silver'       => $user_begin_data['baseSilver'],
                    'user_energy'       => $user_begin_data['baseEnergy'],
                    'user_available_deck'=>serialize($available_deck),
                    'user_cards_in_deck'=> serialize($user_card_deck),
                    'user_level'        => '0',
                    'user_rating'       => '0'
                ]);

                if(!$auth){
                    return redirect(route('user-home'))->withErrors(['Ошибка Авторизацции.']);
                }else{
                    return redirect(route('user-home'));
                }
            }
        }else{
            return redirect(route('user-home'))->withErrors(['Такой пользователь уже существует']);
        }

    }

    //пользователь меняет свои данніе
    protected function userChangeSettings(Request $request){
        if( csrf_token() == $request->input('token')){
            $data = $request->all();

            if($data['action'] == 'user_settings'){
                $email = htmlspecialchars(strip_tags(trim($data['settings_email'])));
                $old_pass = htmlspecialchars(strip_tags(trim($data['current_password'])));
                $new_pass = htmlspecialchars(strip_tags(trim($data['settings_pass'])));
                $new_conf_pass = htmlspecialchars(strip_tags(trim($data['settings_pass_confirm'])));

                $user = Auth::user();

                if(!empty($new_pass)){
                    if($user['password'] == md5($old_pass.$user['login'])){
                        if($new_pass == $new_conf_pass){
                            User::where('id','=',$user['id'])->update(['password' => md5($new_pass.$user['login'])]);
                        }else{
                            return json_encode('Пароль подтвержден неверно.');
                        }
                    }else{
                        return json_encode('Неверный текущий пароль');
                    }

                    if(strlen($new_pass) < 6){
                        return json_encode('Пароль слишком короткий');
                    }
                }

                if(($email != '') && ($email != $user['email'])){
                    $validator = Validator::make($data, [
                        'email'   => 'email'
                    ]);

                    if($validator->fails()){
                        return redirect(route('user-settings'))->withErrors($validator);
                    }

                    User::where('id','=',$user['id'])->update(['email' => $email]);
                }

                if('undefined' != $data['image_user']){
                    //Узнаем реальное имя файла
                    $img_file = $data['image_user']->getClientOriginalName();

                    $path_info = pathinfo($img_file);

                    switch($path_info['extension']){
                        case 'jpeg': $error = 0; break;
                        case 'jpg': $error = 0; break;
                        case 'bmp': $error = 0; break;
                        case 'gif': $error = 0; break;
                        case 'png': $error = 0; break;
                        default: $error = 1;
                    }
                    if(0 == $error){
                        //Указываем папку хранения картинок
                        $destinationPath = base_path().'/public/img/user_images/';

                        $img_file = uniqid().'_'.htmlspecialchars(strip_tags(trim(AdminFunctions::str2url($img_file))));
                        $result = $data['image_user']->move($destinationPath, $img_file);
                        if($result != false){
                            User::where('id','=',$user['id'])->update(['img_url' => $img_file]);
                        }
                    }
                }

                return 'success';

            }

        }
    }
}