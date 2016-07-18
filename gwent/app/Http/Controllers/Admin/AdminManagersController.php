<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\User;

class Admin extends Model{
    protected $guarded = array('id');
    protected $table = 'users';
    protected $hidden = array('password', 'remember_token');
}


class AdminManagersController extends BaseController
{
    public function index(){
        //Выборка всех администраторов, передача их в шаблон admin.managers
        //user_role == 0 -> простой пользователь
        //user_role == 1 -> администратор
        
        $users = User::where('user_role', '!=', '0')->orderBy('login', 'asc')->get();
        return view('admin.managers', ['users' => $users]);
    }
    
    public function addPage(){        
        return view('admin.layout.adds.manager');
    }
    
    public function editPage($id, $errors = null){
        //Выборка пользователя по id, передача его в шаблон admin.layout.edits.manager
        $user = User::where('id', '=', $id)->get();
        return view('admin.layout.edits.manager', ['user' => $user]);
    }
    
    protected function validatePass($pass, $conf_pass){
        //Проверки пароля
        $error = [];
        if(strlen($pass) <= 4){
            $error['pass'] = 'Пароль должен быть больше 6-ти символов.';
        }

        if($pass != $conf_pass){
            $error['conf_pass'] = 'Подтвердите пароль.';
        }
        return $error;
    }
    
    protected function addAdmin(Request $request){
        //Проверка на кроссайтовую передачу данных
        if(csrf_token() == $request->input('_token')){
            $data = $request->all();

            //Выполнение проверки пароля
            $error = $this->validatePass($data['adm_password'], $data['adm_confirm_password']);

            //Выполнение проверки логина
            if(empty($data['adm_login'])){
                $error['login'] = 'Введите логин.';
            }
            
            if(strlen($data['adm_login']) <= 4){
                $error['login'] = 'Логин должен быть больше 4-х символов.';
            }
            
            //Поиск логина пользователя (проверка на уникальность)
            $login_isset = User::where('login', '=', $data['adm_login'])->count();
            //Если пользователь с таким логином уже существует - отчет о  ошибке
            if($login_isset > 0){
                $error['login'] = 'Такой пользователь уже существует.';
            }
          
        }else{
            //Кроссайтовая передача данных обнаружена. отчет о  ошибке
            $error['login'] = 'csrf detected';
        }

        if(!empty($error)){
            //Передача ошибок в шаблон обработчика admin.layout.adds.manager
            return redirect(route('admin-manager-add'))->withErrors([$error]);
        }else{
            //Если всё ОК, заносим в базу администратора
            //Парольформируется методом md5() пароль+логин
            $result = Admin::create([
                'login'    => $data['adm_login'],
                'password' => md5($data['adm_password'].$data['adm_login']),
                'name'     => $data['adm_name'],
                'email'    => $data['adm_email'],
                'user_role'=> '1'
            ]);

            //Если пользователь создан переходим на /admin/admins
            if($result !== false){
                return redirect(route('admin-managers'));
            }
        }
    }
    
    protected function editAdmin(Request $request){
        //Проверка на кроссайтовую передачу данных
        if(csrf_token() == $request->input('_token')){
            $data = $request->all();
            $error = [];
            //Если пароль не указан, то не изменяем его
            if(strlen($data['adm_password']) != 0){
                $error = $this->validatePass($data['adm_password'], $data['adm_confirm_password']);
            }

        }else{
            
            $error['login'] = 'csrf detected';
        }
        if(!empty($error)){
            /*
             * При наличии ошибок вернуться на страницу редактирования
             * администратора с отчетом о ошибках
            */
            return redirect()->back()->withErrors(['errors'=>$error]);
        }else{
            //Выборка из БД текущего пользователя
            $editedAdmin = User::find($request->input('adm_id'));
            //Задаем новый пароль, если он не пуст
            if(strlen($data['adm_password']) != 0){
                $editedAdmin->password  = md5($data['adm_password'].$data['adm_login']);
            }
            $editedAdmin->email         = $data['adm_email'];
            $editedAdmin->name          = $data['adm_name'];
            $editedAdmin->updated_at    = date('Y-m-d H:i:s');

            $result = $editedAdmin->save();
            if($result !== false){
                return redirect(route('admin-managers'));
            }
        }
    }
    
    public function dropAdmin(Request $request){
        if(csrf_token() == $request->input('_token')){
            $dropAdmin = User::find($request->input('adm_id'));
            $result = $dropAdmin -> delete();
            if($result !== false){
                return redirect(route('admin-managers'));
            }
        }
    }
}
