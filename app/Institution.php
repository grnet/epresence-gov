<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
		'title',
		'slug',
		'status',
		'url',
		'shibboleth_domain',
		'contact_name',
		'contact_email',
		'contact_phone'
	];
	
	/**
     * An user institution has many conferences
     */
	public function departments()
	{
		return $this->hasMany('App\Department');
	}

	public function domains()
	{
		return $this->hasMany('App\Domain');
	}

	public function admin()
	{
		return $this->belongsTo('App\User', 'admin_id');	
	}
	
	public function users()
	{
		return $this->belongsToMany('App\User');	
	}
	
	public function adminDepartment()
	{
		$adminDepartment = $this->departments()->where('slug', 'admin')->first();
		
		return $adminDepartment;
	}
	
	public function otherDepartment()
	{
		$otherDepartment = $this->departments()->where('slug', 'other')->first();
		
		return $otherDepartment;
	}
	
	public function institutionAdmins()
	{
		$institutionAdmins = $this->users()->whereHas('roles', 
											function($query){
												$query->whereIn('name', ['InstitutionAdministrator']);
											})
										 ->where('confirmed',true)->where('status',1)->get();
					
		return $institutionAdmins;
	}
	
	public static function advancedSearch($institutions, $input){
		
		$sorting = ['sort_title', 'sort_shibbolethDomain', 'sort_contactName', 'sort_contactEmail', 'sort_contactPhone'];
		$advanced_search = ['shibboleth_domain', 'moderator_firstname', 'moderator_lastname', 'moderator_email'];
		
		if(empty(array_intersect(array_keys($input), $sorting)) && empty(array_intersect(array_keys($input), $sorting)) && empty(array_intersect(array_keys($input), ['id']))){
			$institutions = $institutions->orderBy('title');
		}else{
			foreach($input as $k => $v){
				if($k == 'id' && !empty($v)){
					$institutions = $institutions->where('id', intval($v));
				}elseif(in_array($k, $advanced_search) && !empty($v)){
					if(str_contains($k, 'moderator_')){
						$field = substr($k, 10);
						$users = User::where($field, 'like', '%'.$v.'%')
										->whereHas(
											'roles', function($query){
											$query->where('name', 'InstitutionAdministrator');
											}
										)->pluck($field);
										
						$institutions = $institutions->whereHas(
											'users', function($query) use ($users, $field){
											$query->whereIn($field, $users);
											}
										);
					}else{
						$institutions = $institutions->where($k, 'like', '%'.$v.'%');
					}
				}elseif(str_contains($k, 'sort_')){
					$sort = substr($k, 5);
					if($sort == 'shibbolethDomain'){
						$sort = snake_case('shibbolethDomain');
					}
					$institutions = $institutions->orderBy($sort, $v);
				}
			}
		}
		
		return $institutions;
	}
	
}
