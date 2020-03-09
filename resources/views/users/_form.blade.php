@if(is_null(Session::get('previous_url')))
	{{ Session::put('previous_url', URL::previous()) }}
@endif
	
<!-- Firstname -->
<div class="form-group">
	{!! Form::label('FieldUserName', trans('users.name').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!}">
		{!! Form::text('firstname', null, ['class' => 'form-control', 'id' => 'FieldUserName', 'placeholder' => trans('users.nameRequired')]) !!}
		<div class="help-block with-errors" style="margin:0px;"></div>
	</div>
</div>

<!-- Lastname -->
<div class="form-group">
	{!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!}">
		{!! Form::text('lastname', null, ['class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
		<div class="help-block with-errors" style="margin:0px;"></div>
	</div>
</div>
<div class="form-group">
	{!! Form::label('FieldUserConfirmed',trans('users.confirmed').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		@if($user->confirmed == 0)
			{{trans('users.no')}}
		@else
			{{trans('users.yes')}}
		@endif
	</div>
</div>
<!-- Email -->
@if($user->state == 'sso')
	
<div class="form-group">
	{!! Form::hidden('email', $user->email) !!}
	
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{{ $user->email }}
		<div class="help-block" style="margin:0px;">{{trans('users.emailFixedSso')}}</div>
	</div>
</div>
<?php
$extra_emails_sso = $user->extra_emails_sso()->toArray();
$extra_emails_custom = $user->extra_emails_custom()->toArray();
?>
<div id="xtraMailsList">
	@foreach($extra_emails_sso as $mail)
		<div class="form-group" id="formGroup_{!! $mail['id'] !!}">
			{!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}
			<div class="col-sm-{!! $input !!}" style="color:green; padding-top:7px;">
				{{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
			</div>
		</div>
	@endforeach
	@foreach($extra_emails_custom as $mail)
		<div class="form-group" id="formGroup_{!! $mail['id'] !!}">
			{!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-'.$label]) !!}

			@if($mail['confirmed'] == 1)
			<div class="col-sm-{!! $input !!}" style="color:green; padding-top:7px;">
				{{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailConfirmedShort')}})
			</div>
				@else
				<div class="col-sm-{!! $input !!}" style="color:red; padding-top:7px;">
					{{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})
				</div>
			@endif
		</div>
	@endforeach
</div>
@elseif($user->hasRole('EndUser') && $user->participantInConferences()->count() > 0)
	
<div class="form-group">
	{!! Form::hidden('email', $user->email) !!}
	
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{{ $user->email }}
		<div class="help-block" style="margin:0px;">{{trans('users.emailFixedJoinedOnce')}}</div>
	</div>
</div>
	
@elseif(in_array($user->application, ['none', 'accepted']) && ($user->hasRole('InstitutionAdministrator') || $user->hasRole('DepartmentAdministrator')))
{!! Form::hidden('email', $user->email) !!}

<div class="form-group">
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{{ $user->email }}
		<div class="help-block" style="margin:0px;">{{trans('users.emailOf')}} {{ trans($user->roles()->first()->label) }}{{trans('users.cannotBeChanged')}}</div>
	</div>
</div>

@elseif($user->hasRole('SuperAdmin'))
{!! Form::hidden('email', $user->email) !!}

<div class="form-group">
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{{ $user->email }}
		<div class="help-block" style="margin:0px;">{{trans('users.emailOf')}} {{ $user->roles()->first()->label }} {{trans('users.cannotBeChanged')}}</div>
	</div>
</div>

@elseif(!in_array($user->application, ['none', 'accepted']) && ($user->hasRole('InstitutionAdministrator') || $user->hasRole('DepartmentAdministrator')))

<div class="form-group">
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{!! Form::text('email', null, ['class' => 'form-control','id' => 'FieldUserEmail', 'placeholder' => trans('users.emailRequired')]) !!}
		<div class="help-block" style="margin:0px;">{{trans('users.emailFixedWhenApproved')}}</div>
	</div>
</div>

@else
<div class="form-group">
	{!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-{!! $input !!} form-control-static">
		{!! Form::text('email', null, ['class' => 'form-control','id' => 'FieldUserEmail', 'placeholder' => trans('users.emailRequired')]) !!}
	</div>
</div>

@endif

<!-- Telephone -->	
@if($user->hasRole('EndUser') == false)
	<div class="form-group">
		{!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneRequired')]) !!}
			<div class="help-block with-errors" style="margin:0px;"></div>
		</div>
	</div>
@else
	<div class="form-group">
		{!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
			<div class="help-block with-errors" style="margin:0px;"></div>
		</div>
	</div>
@endif

<!-- State -->
	@if($user->state=='sso' && $user->confirmed == 1)
		<div class="form-group">
			{!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-'.$label]) !!}
			<div class="col-sm-8">
					{{trans('users.no')}}
			</div>
		</div>
		{!! Form::hidden('state', 'sso') !!}
		@else
<div class="form-group">
	{!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-8">
		<label class="radio-inline">
			{!! Form::radio('state', 'local', str_is($user->state, 'local'), ['id' => 'field']) !!}
			{{trans('users.yes')}}
		</label>
		<label class="radio-inline">
			{!! Form::radio('state', 'sso', str_is($user->state, 'sso'), ['id' => 'field']) !!}
			{{trans('users.no')}}
		</label>
	</div>
</div>
@endif

{!! Form::hidden('current_state', $user->state) !!}

<!-- Password -->
@unless($user->state == 'sso' || !in_array($user->application, ['none', 'accepted']))
<div class="form-group has-success">
	{!! Form::label('FieldUserPassword', trans('users.newPassword').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-8">
		{!! Form::checkbox('new_password', 0, true, ['id' => 'FieldUserPassword', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
		<div class="help-block" style="margin:0px; color:#737373;">{{trans('users.sendNewPassword')}}.</div>
	</div>
</div>
@endunless


<!-- Status -->
@if(in_array($user->application, ['none', 'accepted']))				
<div class="form-group has-success">
	{!! Form::label('FieldUserStatus', trans('users.active').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-8">
		<?php $value = Form::getValueAttribute('status'); ?>
		{!! Form::checkbox('status', str_is($user->status, 1), null, ['id' => 'FieldUserStatus', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
	</div>
</div>


@unless($user->state == 'sso' || $user->status == 1)							
<div id="FieldUserStatusAlert" class="alert alert-warning" role="alert" > 
	<span id="FieldUserStatusMessage" style="margin-right:10px">{!!trans('users.activationSelected')!!}: </span>
	{!! Form::checkbox('SendUserEmail', 0, false, ['id' => 'SendUserEmail', 'data-toggle' => 'checkbox-x', 'data-size' => 'sm', 'data-three-state' => 'false']) !!}
    <label class="cbx-label" for="SendUserEmail">{{trans('users.sendPasswordEmail')}}</label>
</div>
@endunless
@endif

<!-- Role -->								
@if ($action ==  'edit' && (Auth::user()->hasRole('SuperAdmin'))  && $user->hasRole('SuperAdmin') == false)
<div class="form-group">
	{!! Form::label('FieldUserRole', trans('users.role').':', ['class' => 'control-label col-sm-2']) !!}
	<div class="col-sm-{!! $input !!}">
			{!! Form::select('role', ['' => ''] + App\User::role_dropdown(['SuperAdmin'], 'name'), $user->roles->first()->name, ['id' => 'FieldUserRole', 'style' => 'width: 100%'])!!}
	</div>
</div>
						
{!! Form::hidden('current_role', $user->roles->first()->name) !!}
						
@else							
{!! Form::hidden('current_role', $user->roles->first()->name) !!}
{!! Form::hidden('role', $user->roles->first()->name) !!}
						
@endif

<!-- Institution -->
@if (Auth::user()->hasRole('SuperAdmin'))
							
	@if(($user->state == 'local' || $user->confirmed == 0) && $user->institutions->count() > 0 && $user->institutions->first()->slug != 'other')
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $user->institutions->first()->id, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
		</div>
	</div>
							
	<div class="form-group" id="UserOrgNew">
		{!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
			<div class="help-block with-errors" style="margin:0px;"></div>
		</div>
	</div>
	
	@elseif(($user->state == 'local' || $user->confirmed == 0) && $user->institutions->count() > 0 && $user->institutions->first()->slug == 'other')
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
		</div>
	</div>
							
	<div class="form-group" id="UserOrgNew">
		{!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_institution', $user->customValues()['institution'], ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
			<div class="help-block with-errors" style="margin:0px;"></div>
		</div>
	</div>
							
	@elseif(($user->state == 'local' || $user->confirmed == 0) && $user->institutions->count() == 0)
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], null, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
		</div>
	</div>
							
	<div class="form-group" id="UserOrgNew">
		{!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
			<div class="help-block with-errors" style="margin:0px;"></div>
		</div>
	</div>
	
	@elseif($user->state == 'sso' && $user->confirmed = 1)
	
	{!! Form::hidden('institution_id', isset($user->institutions->first()->id) ? $user->institutions->first()->id : null) !!}
	
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!} form-control-static">
			{{ $user->institutions->first()->title }}
			<div class="help-block" style="margin:0px; text-align:left !important;">{{trans('users.institutionFixedSso')}}</div>
		</div>
	</div>
							
	@endif
	
@else
	
	{!! Form::hidden('institution_id', isset($user->institutions->first()->id) ? $user->institutions->first()->id : null) !!}
								
	@if($user->institutions->first()->slug == 'other')
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!} form-control-static">
			{{ $user->institutions->first()->title }} ({{ $user->customValues()['institution'] }})
		</div>
	</div>
							
	@else
	<div class="form-group">
		{!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!} form-control-static">
			{{ $user->institutions->first()->title }}
		</div>
	</div>
	
	@endif
	
@endif

<!-- Department -->
@if ((Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator')) && ($user->hasRole('EndUser') || $user->hasRole('DepartmentAdministrator')))
							
	@if($user->departments()->count() > 0 && $user->departments()->first()->slug == 'other')
	<div class="form-group" id="UserDepart">
		{!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
		</div>
	</div>
							
	<div class="form-group" id="UserDepartNew">
		{!! Form::label('FieldUserDepartNew', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_department', $user->customValues()['department'], ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
			<div id="newDepDiv" class="help-block with-errors newdep alert alert-warning" style="margin:0px;">{{ trans('users.newDeptWarning') }}</div>
		</div>
	</div>
							
	@elseif($user->departments()->count() > 0 && $user->departments()->first()->slug != 'other')
	<div class="form-group" id="UserDepart">
		{!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions()->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $user->departments->first()->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
		</div>
	</div>
	
	<div class="form-group" id="UserDepartNew">
		{!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
			<div id="newDepDiv" class="help-block with-errors newdep alert alert-warning" style="margin:0px;">{{ trans('users.newDeptWarning') }}</div>
		</div>
	</div>
	
	@elseif($user->state == 'sso' && $user->departments()->count() == 0 && $user->institutions()->count() > 0)
	
	<div class="form-group" id="UserDepart">
		{!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $user->institutions()->first()->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], null , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
		</div>
	</div>
	
	<div class="form-group" id="UserDepartNew">
		{!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
			<div id="newDepDiv" class="help-block with-errors newdep alert alert-warning" style="margin:0px;">{{ trans('users.newDeptWarning') }}</div>
		</div>
	</div>	

	@else
	<div class="form-group" id="UserDepart">
		{!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::select('department_id', ['' => ''] + App\Department::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], null, ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
		</div>
	</div>
	
	<div class="form-group" id="UserDepartNew">
		{!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-'.$label]) !!}
		<div class="col-sm-{!! $input !!}">
			{!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
			<div id="newDepDiv" class="help-block with-errors newdep alert alert-warning" style="margin:0px;">{{ trans('users.newDeptWarning') }}</div>
		</div>
	</div>	
	
	@endif
							
@elseif (Auth::user()->hasRole('DepartmentAdministrator') || ($user->hasRole('EndUser') || $user->hasRole('DepartmentAdministrator')))
						
	{!! Form::hidden('department_id', isset($user->departments->first()->id) ? $user->departments->first()->id : null) !!}
	
@else
						
	{!! Form::hidden('department_id', isset($user->departments->first()->id) ? $user->departments->first()->id : null) !!}
							
							
@endif

@if (Auth::user()->hasRole('SuperAdmin'))
<!-- Description -->							
<div class="form-group" id="FieldCoordDepartDepartFormGroup">
	{!! Form::label('FieldCoordDepartComment', trans('users.description').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-6">
		{!! Form::textarea('comment', null, ['class' => 'form-control', 'id' => 'FieldCoordDepartComment', 'rows' => '3', 'readonly'])!!}
	</div>
</div>

@if(in_array($user->application, ['new', 'inProgress', 'notVarified']))
<!-- Application -->

	@if($user->application == 'new')
		<div class="form-group">
			{!! Form::label('FieldApplicationStatus', trans('users.application').':', ['class' => 'control-label col-sm-'.$label]) !!}
			<div class="col-sm-6">
				{!! Form::select('application', ['' => '', 'inProgress' => trans('users.inProgress'), 'accepted' => trans('users.accepted'), 'notVerified' => trans('users.rejected')], null, ['id' => 'FieldApplicationStatus', 'style' => 'width: 100%'])!!}
			</div>
		</div>
	@else
		<div class="form-group">
			{!! Form::label('FieldApplicationStatus', trans('users.application').':', ['class' => 'control-label col-sm-'.$label]) !!}
			<div class="col-sm-6">
				{!! Form::select('application', ['' => '', 'inProgress' => trans('users.inProgress'), 'accepted' => trans('users.accepted'), 'notVerified' => trans('users.rejected')] , $user->application, ['id' => 'FieldApplicationStatus', 'style' => 'width: 100%'])!!}
			</div>
		</div>
	@endif

{!! Form::hidden('application_current', $user->application) !!}
@endif

<!-- Admin Comment -->							
<div class="form-group" id="FieldCoordDepartDepartFormGroup">
	{!! Form::label('FieldCoordDepartAdminComment', trans('users.comments').':', ['class' => 'control-label col-sm-'.$label]) !!}
	<div class="col-sm-6">
		{!! Form::textarea('admin_comment', null, ['class' => 'form-control', 'placeholder' => trans('users.moderatorComments'), 'rows' => '3', 'id' => 'FieldCoordDepartAdminComment'])!!}
	</div>
</div>

@endif
							
{!! Form::hidden('institution_id_current', isset($user->institutions->first()->id) ? $user->institutions->first()->id : null) !!}
{!! Form::hidden('department_id_current', isset($user->departments->first()->id) ? $user->departments->first()->id : null) !!}
{!! Form::hidden('from_page', $from_page) !!}
{!! Form::hidden('confirmed', $user->confirmed) !!}
							
<div class="row">
	<div class="col-sm-12">
		<span class="pull-right">   
			<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">	
				{!! Form::submit($submitBtn, ['class' => 'btn btn-primary', 'name' => 'add_details']) !!}
			</div>
		@if(Auth::user()->hasRole('SuperAdmin'))
			@if($user->canBeDeleted())
				<a href="/users/delete/{{ $user->id }}"><button type="button" class="btn btn-danger" id="TeleReturn" >{{trans('users.deleteUser')}}</button></a>
			@elseif(!$user->canBeDeleted())
				<button type="button" class="btn btn-danger" id="TeleReturn" data-toggle="tooltip" data-placement="top" title="{{trans('users.userFixedJoinedOnce')}}">{{trans('users.deleteUser')}}</button>
			@endif
		@endif
			<a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default" id="TeleReturn" >{{trans('users.return')}}</button></a>
		</span>
	</div>
</div>
