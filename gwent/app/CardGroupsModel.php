<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CardGroupsModel extends Model{
    protected $table = 'tbl_card_groups';
    protected $fillable = ['title', 'slug','has_cards_ids', 'secial_abilities'];
}
