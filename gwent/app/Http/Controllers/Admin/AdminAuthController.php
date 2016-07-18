<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\User;
use Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminAuthController extends BaseController
{
    public function getLogin(){
        return view('admin.login');
    }
    
    public function login(Request $request){
        $data = $request->all();
        //Проверка на кроссайтовую передачу данных
        if(csrf_token() == $request->input('_token')){

            //Валидация полей логина и пароля
            $validator = Validator::make($data, [
                'username' => 'required|max:255',
                'password' => 'required|min:4',
            ]);
            
            /*
             * Если данные не прошли валидацию, отправляем отчет
             * о ошибках на admin/login
            */
            if($validator->fails()){
		        return redirect(route('admin-login'))->withErrors($validator);
            }
            
            $login = $data['username'];
            $password = md5($data['password'].$data['username']);
            
            //Выборка пользователя из БД
            $user = User::where('login', '=', $login )->where('password', '=', $password)->where('user_role', '=', '1')->get();

            //Если пользователь существует, проводим аутентификацию
            if(isset($user[0])){                
                $auth = Auth::loginUsingId($user[0]->id);
            }else{
                //Если не существует, отправляем отчет о ошибках на admin/login
                return redirect(route('admin-login'))->withErrors(['Нет такого пользователя']);
            }

            if(!$auth){
                //При сбое авторизации, отправляем отчет о ошибках на admin/login
		        return redirect(route('admin-login'))->withErrors(['Ошибка авторизации']);
            }
            //если всё ОК перходим на главную админки
            return redirect(route('admin-main'));
        }
    }
    
    public function logout(){
        Auth::logout();
	    return redirect(route('user-home'));
    }
}

