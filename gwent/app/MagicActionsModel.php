<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MagicActionsModel extends Model{
    protected $table = 'tbl_magic_actions';
    protected $fillable = ['title', 'description','slug','html_options'];
}
