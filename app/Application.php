<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Application extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'applications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'role_id', 'user_state', 'app_state'];
	
	/**
     * An application belongs to one user
     */
	public function user()
	{
		return $this->belongsTo('App\User','user_id');
	}
	
	/**
     * An application belongs to one role
     */
	public function role()
	{
		return $this->belongsTo('App\Role','role_id');
	}

    public function institution()
    {
        return $this->belongsTo('App\Institution','institution_id');
    }


    public function department()
    {
        return $this->belongsTo('App\Department','department_id');
    }



    public function getStatusString()
    {
        if ($this->app_state == 'new') {
            return trans('application.newFemale');
        } elseif ($this->app_state == 'notVerified') {
            return trans('application.rejected');
        } elseif ($this->app_state == 'inProgress') {
            return trans('application.inProgress');
        }
    }

    public function customValues()
    {
        $customValues = ['institution' => null, 'department' => null];

        if ($this->custom_values != null) {
            $customValues = ['institution' => htmlspecialchars_decode(json_decode($this->custom_values)->institution, ENT_QUOTES), 'department' => htmlspecialchars_decode(json_decode($this->custom_values)->department, ENT_QUOTES)];
        }

        return $customValues;
    }
}
