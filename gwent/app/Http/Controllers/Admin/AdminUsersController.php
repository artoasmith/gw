<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;

class AdminUsersController extends BaseController
{
    //списоk юзеров
    public function index(){
        $users = User::where('user_role', '=', 0)->orderBy('id','DESC')->paginate(20);
        return view('admin.users',['users'=>$users]);
    }

    //user view
    public function view($id){
        $user = User::where('user_role', '=', 0)->where('id', '=', $id)->first();
        if(!$user)
            return 404;

        return view('admin.usersView',['user'=>$user]);
    }

    //бан юзеров
    public function ban(Request $request){
        $data= $request->all();
        $id = (isset($data['id'])?intval($data['id']):0);
        $status = (isset($data['status'])?boolval($data['status']):false);
        $user = User::where('user_role', '=', 0)->where('id', '=', $id)->first();
        if($user){
            $user->is_banned = $status;
            $user->save();
        }
    }

    //удаление юзера
    public function deleteUser(Request $request){
        //check permission
        if(csrf_token() != $request->input('_token'))
            return false;

        //drop object
        $user = User::where('user_role', '=', 0)->where('id', '=', $request->input('id'))->first();
        $result = ($user?$user -> delete():false);

        //response
        return ($result!==false?redirect(route('admin-users')):'Не удалось удалить карту из базу.');
    }
}