<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleLogModel extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'battle_id', 'fight_log'
    ];
}
