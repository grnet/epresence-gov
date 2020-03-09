<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Backpack\CRUD\CrudTrait;

class Video extends Model
{
    use CrudTrait;


    protected $fillable = ['title_el', 'title_en', 'youtube_video_id','order'];
}
