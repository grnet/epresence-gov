<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class NamedUser extends Model
{
    //This model refers to zoom api users

    protected $fillable = [
        'email',
        'latest_used',
        'type'
    ];


    //Conferences made using this named user

    public function conferences()
    {
        return $this->hasMany('App\Conference', 'named_user_id');
    }

    //Returns the next available Named User (zoom user) in line to assign him to the conference created
    //This runs while a conference is being created

    /**
     * @param $start
     * @param $end
     * @param string $type
     * @return bool|null
     */
    public static function get_next_named_user_in_line($start, $end, $type = 'conferences')
    {
        $suitable_named_user = null;
        $available_named_users = self::whereDoesntHave('conferences', function ($conferences_query) use ($start, $end) {
            $conferences_query->where(function ($where_q) use ($start, $end) {
                $where_q->where('end', '>=', $start)->where('start', '<=', $end)->whereNull('forced_end');
            })->orWhere(function ($where_q) use ($start, $end) {
                $where_q->whereNotNull('forced_end')->where('forced_end', '>=', $start)->where('start', '<=', $end);
            });
        })->where('type', $type)->orderBy('id', 'asc')->get();

        $minimum = 0;
        foreach ($available_named_users as $named_user) {
            $first_conference_behind = $named_user->conferences()->where("end","<=",$start)->orderBy("end","desc")->first();
            $first_conference_in_front = $named_user->conferences()->where("start",">=",$end)->orderBy("start","asc")->first();

            $interval_behind = isset($first_conference_behind->id) ? $first_conference_behind->end->diffInMinutes($start) : 999999999;
            $interval_after = isset($first_conference_in_front->id) ? $first_conference_in_front->start->diffInMinutes($end) : 999999999;

            $named_int_min = $interval_behind < $interval_after ? $interval_behind : $interval_after;

            if($named_int_min > $minimum){
                $minimum = $named_int_min;
                $suitable_named_user = $named_user;
            }
        }
        if (isset($suitable_named_user->id)) {
            self::where('latest_used', true)->update(['latest_used' => false]);
            $suitable_named_user->update(['latest_used' => true]);

            return $suitable_named_user;
        } else {
            return false;
        }
    }

}
