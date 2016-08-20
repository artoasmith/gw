<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CardsModel extends Model{
    protected $table = 'tbl_card';
    protected $fillable = [
        'title', 'slug', 'card_type', 'forbidden_races', 'card_race', 'allowed_rows',
        'card_strong', 'card_value', 'is_leader', 'img_url', 'card_actions',
        'card_groups', 'max_quant_in_deck', 'short_description', 'full_description',
        'price_gold', 'price_silver', 'price_only_gold'
    ];
}
