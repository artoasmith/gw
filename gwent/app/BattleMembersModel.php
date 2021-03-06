<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class BattleMembersModel extends Model{
    protected $table = 'tbl_battle_members';
    protected $fillable = [
        'user_id', 'battle_id', 'user_deck_race', 'user_deck', 'user_hand', 'magic_effects', 'user_energy',
        'user_ready', 'round_passed', 'card_source', 'user_discard', 'card_to_play'
    ];
}
