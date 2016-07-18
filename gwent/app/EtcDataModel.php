<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class EtcDataModel extends Model{
    protected $table = 'tbl_etc_data';
    protected $fillable = [
        'label_data', 'meta_key', 'meta_value', 'meta_key_title'
    ];
}
