{!! trans('emails.admin_application',
[
"requested_role"=>trans($role_requested,[],'el'),
"user_email"=>$user['email'],
"user_state"=>$user['state'] == "local" ? trans('application.yes',[],'el') : trans('application.no',[],'el'),
"applications_url"=>url('/administrators/applications'),
"institution"=>$user['institution_title'],
"department"=>isset($user['department_title'])?$user['department_title']:null,
"telephone"=>$user['telephone'],
"user_firstname"=>$user['firstname'],
"user_lastname"=>$user['lastname'],
"comment"=>$user['comment']
],'el') !!}