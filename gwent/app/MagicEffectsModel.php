<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class MagicEffectsModel extends Model{
    protected $table = 'tbl_magic_effects';
    protected $fillable = ['title', 'slug', 'img_url', 'description', 'energy_cost', 'price_gold', 'price_silver', 'usage_count', 'effect_actions', 'race'];
}
