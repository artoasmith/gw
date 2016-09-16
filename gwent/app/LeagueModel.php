<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class LeagueModel extends Model{
    protected $table = 'tbl_league';
    protected $fillable = [
        'title', 'min_lvl', 'max_lvl'
    ];
}
