@if($user->roles->first()->name == "EndUser")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"Χρήστη"],'el') !!}
@elseif($user->roles->first()->name == "DepartmentAdministrator")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"Συντονιστή Τμήματος"],'el') !!}
@elseif($user->roles->first()->name == "InstitutionAdministrator")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"Συντονιστή Οργανισμού"],'el') !!}
@endif
{!! trans('emails.enable_account_local.intro',["user_email"=>$user->email,"password"=>$password,"login_url"=>$login_url],'el') !!}
@if($user->confirmed == 0)
    {!! trans('emails.enable_account_local.unconfirmed',[],'el') !!}
@endif

@if($user->roles->first()->name == 'EndUser')
    {!! trans('emails.enable_account_local.end_user_message',[],'el') !!}
@endif
{!! trans('emails.enable_account_local.outro',[],'el') !!}
<hr>
@if($user->roles->first()->name == "EndUser")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"User"],'en') !!}
@elseif($user->roles->first()->name == "DepartmentAdministrator")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"Department Moderator"],'en') !!}
@elseif($user->roles->first()->name == "InstitutionAdministrator")
    {!! trans('emails.enable_account_local.pre_intro',["role"=>"Organization  Moderator"],'en') !!}
@endif
{!! trans('emails.enable_account_local.intro',["user_email"=>$user->email,"password"=>$password,"login_url"=>$login_url],'en') !!}
@if($user->confirmed == 0)
    {!! trans('emails.enable_account_local.unconfirmed',[],'en') !!}
@endif

@if($user->roles->first()->name == 'EndUser')
    {!! trans('emails.enable_account_local.end_user_message',[],'en') !!}
@endif
{!! trans('emails.enable_account_local.outro',[],'en') !!}