{!! trans('emails.conference_participant_deleted_coordinators',
[
"conference_date_start"=>$conference->start->format('d-m-Y'),
"conference_title"=>$conference->title,
"deleted_user_email"=>$deleted_user->email
],'el') !!}