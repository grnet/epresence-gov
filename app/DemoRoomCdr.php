<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DemoRoomCdr extends Model

{
    protected $table = 'demo_room_cdrs';

    protected $fillable = ['join_time','leave_time','user_id','zoom_meeting_id'];

    protected $dates = [
        'join_time','leave_time'
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
