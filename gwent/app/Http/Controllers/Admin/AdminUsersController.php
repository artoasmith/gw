<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserAdditionalDataModel;

class AdminUsersController extends BaseController
{
    //списоk юзеров
    public function index(){
        $users = User::orderBy('id','DESC')->paginate(20);
        return view('admin.users',['users'=>$users]);
    }

    //user view
    public function view($id){
        $user = User::where('id', '=', $id)->first();
        if(!$user)
            return 404;

        return view('admin.layout.edits.user',['user'=>$user]);
    }

    //бан юзеров
    public function ban(Request $request){
        $data= $request->all();
        $id = (isset($data['id'])?intval($data['id']):0);
        $status = (isset($data['status'])?boolval($data['status']):false);
        $user = User::find($id);
        if($user){
            $user->is_banned = $status;
            $user->save();
        }
    }


    protected function editAdmin(Request $request){
        $data = $request->all();

        $user = User::find($data['user_id']);
        $user -> email      = $data['user_email'];
        $user -> nickname   = $data['user_nickname'];
        $user -> name       = $data['user_name'];
        $user -> birth_date = $data['user_birthday'];
        $user -> user_gender= $data['user_gender'];
        $user -> address    = $data['user_address'];
        if(isset($data['user_role'])){
            $user -> user_role = 1;
        }else{
            $user -> user_role = 0;
        }
        $user -> save();

        UserAdditionalDataModel::where('user_id', '=', $data['user_id'])->update(['user_gold' => $data['user_gold']]);
        UserAdditionalDataModel::where('user_id', '=', $data['user_id'])->update(['user_silver' => $data['user_silver']]);
        UserAdditionalDataModel::where('user_id', '=', $data['user_id'])->update(['user_energy' => $data['user_energy']]);

        return redirect(route('admin-users'));
    }



    //удаление юзера
    public function deleteUser(Request $request){
        $data = $request -> all();
        $result = User::find($data['id']);
        $result -> delete();
        if($result !== false){
            return redirect(route('admin-users'));
        }
    }
}