<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleModel extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'players_decks', 'deck_weight', 'league', 'fight_status', 'player_num_turn', 'round_status', 'fight_log'
    ];
}
