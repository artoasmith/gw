<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'login', 'email', 'password', 'nickname', 'name', 'birth_date', 'user_gender',
        'address', 'img_url', 'is_banned', 'ban_time', 'user_role', 'user_online',
        'user_is_playing', 'user_current_deck', 'is_activated', 'activation_code'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
