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

            if($user[0]->is_activated == 0){
                return redirect(route('user-home'))->withErrors(['Вы не подтвердили регистрацию по почте.']);
            }

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

        if(!preg_match("/^[a-zA-Z][a-zA-Z0-9_\-]{5,255}$/", $login)){
            return redirect(route('user-registration'))->withErrors(['Логин пользователя содержит<br>запрещеные символы.<br>Разрешено использовать латинские буквы,<br>цифры, символы "_" и "-"']);
        }

        $activation_code = str_random(32);
        //Если такого пользователя не существует
        if(0 == $user){
            //Создаем пользователя и заносим его в таблицу с основными данными users
            $new_user = User::create([
                'login'         => $login,
                'email'         => $email,
                'password'      => $password,
                'is_banned'     => '0',
                'ban_time'      => '0000-00-00 00:00:00',
                'user_role'     => '0',
                'user_online'   => '1',
                'is_activated'  => 1,
                'activation_code' => $activation_code
            ]);

            //Если пользователь создан успешно
            if($new_user !== false){

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
                    'user_id'           => $new_user->id,
                    'login'             => $login,
                    'email'             => $email,
                    'user_base_race'    => $data['fraction_select'],
                    'user_gold'         => $user_begin_data['baseGold'],
                    'user_silver'       => $user_begin_data['baseSilver'],
                    'user_energy'       => $user_begin_data['baseEnergy'],
                    'user_available_deck'=>serialize($available_deck),
                    'user_cards_in_deck'=> serialize($user_card_deck),
                    'user_magic_effects'=> 'a:0:{}',
                    'user_level'        => '0',
                    'user_rating'       => '0'
                ]);

                if($result !== false){
                    \Mail::send('email.welcome', ['code' => $activation_code], function($mess) use ($new_user){
                        $mess -> from('dragon_heart@xmail.com');
                        $mess -> to($new_user->email)-> subject('Подтвердите регистрацию');
                    });
                    return redirect(route('user-home'))->withErrors(['Регистрация почти завершена.<br>Вам необходимо подтвердить e-mail, указанный при регистрации, перейдя по ссылке в письме.']);
                }
            }
        }else{
            return redirect(route('user-home'))->withErrors(['Такой пользователь уже существует']);
        }

    }

    //пользователь меняет свои данные
    protected function userChangeSettings(Request $request){
        if( csrf_token() == $request->input('token')){
            $data = $request->all();

            if($data['action'] == 'user_settings'){
                $email = htmlspecialchars(strip_tags(trim($data['settings_email'])));
                $old_pass = htmlspecialchars(strip_tags(trim($data['current_password'])));
                $new_pass = htmlspecialchars(strip_tags(trim($data['settings_pass'])));
                $new_conf_pass = htmlspecialchars(strip_tags(trim($data['settings_pass_confirm'])));

                $name = htmlspecialchars(strip_tags(trim($data['user_name'])));
                $birth_date = htmlspecialchars(strip_tags(trim($data['birth_date'])));
                $gender = htmlspecialchars(strip_tags(trim($data['gender'])));

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

                if(!empty($name)){
                    User::where('id','=',$user['id'])->update(['name' => $name]);
                }

                if(!empty($birth_date)){
                    User::where('id','=',$user['id'])->update(['birth_date' => $birth_date]);
                }

                if(!empty($gender)){
                    User::where('id','=',$user['id'])->update(['user_gender' => $gender]);
                }

                return 'success';

            }

        }
    }


    protected function confirmAccessToken($token){
        $token = strip_tags(htmlspecialchars(trim($token)));
        $user = \DB::table('users')->select('id','is_activated','activation_code')->where('is_activated', '=', 0)->where('activation_code', '=', $token)->get();

        if($user){

            if($user[0]->is_activated == 1){
                return redirect(route('user-home')->withErrors(['Вы уже активировали данный аккаунт.']));
            }

            $uset_to_activate = User::find($user[0] -> id);
            $uset_to_activate -> is_activated = 1;
            $uset_to_activate -> save();

            $auth = Auth::loginUsingId($user[0]->id);
            if(!$auth){
                return redirect(route('user-home'))->withErrors(['Ошибка авторизации']);
            }else{
                User::where('id', '=', $user[0]->id)->update(['user_online' => 1]);
                return redirect(route('user-home'));
            }

        }else{
            return redirect(route('user-home'))->withErrors(['Произошел сбой в подтверждении регистрации. Обратитесь в тех. поддержку.']);
        }
    }
}