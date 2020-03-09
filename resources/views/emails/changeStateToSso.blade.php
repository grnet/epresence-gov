{!! trans('emails.change_state_to_sso.intro',["login_url"=>$login_url],'el') !!}
@if($user->roles->first()->name == 'EndUser')
{!! trans('emails.change_state_to_sso.end_user_message',[],'el') !!}
@endif
{!! trans('emails.change_state_to_sso.outro',[],'el') !!}
<hr>
{!! trans('emails.change_state_to_sso.intro',["login_url"=>$login_url],'en') !!}
@if($user->roles->first()->name == 'EndUser')
{!! trans('emails.change_state_to_sso.end_user_message',[],'en') !!}
@endif
{!! trans('emails.change_state_to_sso.outro',[],'en') !!}


