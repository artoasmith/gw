<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleModel extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'player_1_id', 'player_2_id', 'player_1_deck', 'player_2_deck',
        'deck_weight', 'league', 'fight_status', 'player_id_turn', 'round_status', 'fight_log'
    ];
}
