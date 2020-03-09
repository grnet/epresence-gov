{!! trans('emails.new_department_administrator',
[
"creator_firstname"=>$user->creator->firstname,
"creator_lastname"=>$user->creator->lastname,
"creator_email"=>$user->creator->email,
"user_id"=>$user->id ,
"user_firstname"=>$user->firstname,
"user_lastname"=>$user->lastname,
"user_email"=>$user->email,
"user_state"=>$user->state == 'local' ? trans('application.yes',[],'el') : trans('application.no',[],'el'),
"user_telephone"=>$user-> telephone,
"user_institution"=>$user->institutions()->first()->title,
"user_department"=>$user->departments()->first()->title,
],'el') !!}
<hr/>
{!! trans('emails.new_department_administrator',
[
"creator_firstname"=>$user->creator->firstname,
"creator_lastname"=>$user->creator->lastname,
"creator_email"=>$user->creator->email,
"user_id"=>$user->id ,
"user_firstname"=>$user->firstname,
"user_lastname"=>$user->lastname,
"user_email"=>$user->email,
"user_state"=>$user->state == 'local' ? trans('application.yes',[],'en') : trans('application.no',[],'en'),
"user_telephone"=>$user-> telephone,
"user_institution"=>$user->institutions()->first()->title,
"user_department"=>$user->departments()->first()->title,
],'en') !!}




