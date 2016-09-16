<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAdditionalDataModel extends Model{
    protected $table = 'tbl_user_data';
    protected $fillable = [
        'user_id', 'login', 'email', 'user_base_race',
        'user_gold', 'user_silver', 'user_energy',
        'user_available_deck', 'user_cards_in_deck',
        'user_magic_effects', 'user_level', 'user_rating'
    ];
}
