<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public function permissions()
	{
		return $this->belongsToMany(Permission::class);
	}
	
	public function givePermission(Permission $permission)
	{
		return $this->permissions()->save($permission);
	}
	
	/**
     * A role may have many applications
     */
	public function applications()
	{
		return $this->hasMany('App\Application');
	}
}
