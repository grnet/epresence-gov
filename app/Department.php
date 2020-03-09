<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
		'institution_id',
		'title',
		'slug',
		'status'
	];
	
	public function institution()
	{
		return $this->belongsTo('App\Institution');	
	}
	
	
	public function users()
	{
		return $this->belongsToMany('App\User');	
	}
	
	public function departmentAdmins()
	{
		$departmentAdmins = $this->users()->whereHas('roles', 
											function($query){
												$query->whereIn('name', ['DepartmentAdministrator']);
											})
										 ->where('confirmed',true)->where('status',1)->get();
					
		return $departmentAdmins;
	}
	
	public static function advancedSearch($departments, $input){
		
		$sorting = ['sort_title'];
		$advanced_search = ['url', 'contact_name', 'contact_email', 'contact_phone'];
		
		if(empty(array_intersect(array_keys($input), $sorting)) && empty(array_intersect(array_keys($input), $sorting)) && empty(array_intersect(array_keys($input), ['id']))){
			$departments = $departments->orderBy('title');
		}else{
			foreach($input as $k => $v){
				if($k == 'id' && !empty($v)){
					$departments = $departments->where('id', intval($v));
				}elseif(in_array($k, $advanced_search) && !empty($v)){
					$departments = $departments->where($k, 'like', '%'.$v.'%');
				}elseif(str_contains($k, 'sort_')){
					$sort = substr($k, 5);
					if(in_array($sort, ['contactName', 'contactEmail', 'contactPhone'])){
						$sort = snake_case($sort);
					}
						
					$departments = $departments->orderBy($sort, intval($v));
				}
			}
		}
		
		return $departments;
	}

}
