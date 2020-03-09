<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    //

    protected $table = 'domains';



    public function institution()
    {
        return $this->belongsTo('App\Institution');
    }










}
