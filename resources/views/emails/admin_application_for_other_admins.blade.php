{!! trans('emails.admin_application_for_other_admins.intro',
[
"role_requested"=>trans($role_requested,[],'el'),
"user_email"=>$user['email'],
"institution"=>$user['institution_title'],
"department"=>$user['department_title'],
"user_telephone"=>$user['telephone'],
"user_firstname"=>$user['firstname'],
"user_lastname"=>$user['lastname'],
 ],'el') !!}
@if($user['state'] == "local")
    {!! trans('emails.admin_application_for_other_admins.local',[],'el') !!}
@else
    {!! trans('emails.admin_application_for_other_admins.sso',[],'el') !!}
@endif
{!! trans('emails.admin_application_for_other_admins.outro',["role_requested"=>trans($role_requested,[],'el'),"user_comment"=>$user['comment']],'el') !!}