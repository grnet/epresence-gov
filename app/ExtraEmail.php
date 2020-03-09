<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtraEmail extends Model
{
    protected $table = 'users_extra_emails';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * An email belongs to a user
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }


}
