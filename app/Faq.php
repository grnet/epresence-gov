<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Faq extends Model
{
    use CrudTrait;

    protected $fillable = ['el_question', 'en_question', 'el_answer', 'en_answer','order','active'];

    public function scopeActive($query){
        return $query->where('active',true);
    }

}
