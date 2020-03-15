{!! trans('emails.application_accepted_for_admins.intro',
[
"institution"=>$user['institution_title'],
"department"=>$user['department_title'] or null,
"user_telephone"=>$user['telephone'],
"user_firstname"=>$user['firstname'],
"user_lastname"=>$user['lastname'],
"user_email"=>$user['email'],
 ],'el') !!}
{!! trans('emails.application_accepted_for_admins.outro',["role_requested"=>trans($role_requested,[],'el'),"user_comment"=>$user['comment']],'el') !!}
