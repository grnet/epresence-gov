{!! trans('emails.coordinators_mail_intro',
[
"sender_firstname"=>$sender->firstname,
"sender_lastname"=>$sender->lastname,
"coordinator_firstname"=>$coordinator->firstname,
"coordinator_lastname"=>$coordinator->lastname
],'el') !!}
{!! $body !!}
