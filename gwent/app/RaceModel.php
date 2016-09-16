<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class RaceModel extends Model{
    protected $table = 'tbl_race';
    protected $fillable = [
        'title', 'slug', 'img_url', 'race_type', 'base_card_deck', 'description_title', 'description', 'position'
    ];
}
