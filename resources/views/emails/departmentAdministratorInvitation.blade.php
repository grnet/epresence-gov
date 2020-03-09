{!! trans('emails.department_administrator_invitation',
[
"inviting_user_firstname"=>$inviting_user->firstname,
"inviting_user_lastname"=>$inviting_user->lastname,
"inviting_user_email"=>$inviting_user->email,
"department"=>$department,
"form_url"=>url('/access_'.$user->state.'_login')
],'el') !!}
<hr/>
{!! trans('emails.department_administrator_invitation',
[
"inviting_user_firstname"=>$inviting_user->firstname,
"inviting_user_lastname"=>$inviting_user->lastname,
"inviting_user_email"=>$inviting_user->email,
"department"=>$department,
"form_url"=>url('/access_'.$user->state.'_login')
],'en') !!}
