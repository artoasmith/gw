<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class CardActionsModel extends Model{
    protected $table = 'tbl_card_actions';
    protected $fillable = ['title', 'description','slug','html_options'];
}
