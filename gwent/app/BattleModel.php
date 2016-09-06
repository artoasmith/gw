<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleModel extends Model{
    protected $table = 'tbl_battles';
    protected $fillable = [
        'creator_id', 'players_quantity', 'deck_weight', 'league', 'fight_status',
        'user_id_turn', 'round_status', 'battle_field', 'undead_cards'
    ];
}
