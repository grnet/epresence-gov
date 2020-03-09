<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cdr extends Model

{
    protected $table = 'cdrs';

    protected $fillable = ['device','join_time','leave_time','user_id','conference_id'];

    protected $dates = [
        'join_time','leave_time'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
