{!! trans('emails.conference_participant_delete',
[
"conference_title"=>$conference->title,
"conference_date_start"=>$conference->getDate($conference->start),
"conference_time_start"=>$conference->getTime($conference->start),
"conference_date_end"=>$conference->getDate($conference->start),
"conference_time_end"=>$conference->getTime($conference->start),
"invited_by_lastname"=>$conference->user->lastname,
"invited_by_firstname"=>$conference->user->firstname,
"invited_by_email"=>$conference->user->email,
"invited_by_telephone"=>$conference->user->telephone,
],'el') !!}
<hr/>
{!! trans('emails.conference_participant_delete',
[
"conference_title"=>$conference->title,
"conference_date_start"=>$conference->getDate($conference->start),
"conference_time_start"=>$conference->getTime($conference->start),
"conference_date_end"=>$conference->getDate($conference->start),
"conference_time_end"=>$conference->getTime($conference->start),
"invited_by_lastname"=>$conference->user->lastname,
"invited_by_firstname"=>$conference->user->firstname,
"invited_by_email"=>$conference->user->email,
"invited_by_telephone"=>$conference->user->telephone,
],'en') !!}