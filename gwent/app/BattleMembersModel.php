<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleMembersModel extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'user_id', 'battle_id', 'user_deck', 'magic_abilities', 'user_energy'
    ];
}
