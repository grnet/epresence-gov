<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;

class Email extends Model
{

    use CrudTrait;


    protected $fillable = [
		'name',
		'title',
		'body',
		'sender_email'
	];
	
	public function hasName($name)
	{
		if(is_string($name)){
			return $this->contains('name', $name)->first();
		}
		
		return false;
	}
}
