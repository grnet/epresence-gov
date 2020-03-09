@if(is_null(Session::get('previous_url')))
    {{ Session::put('previous_url', URL::previous()) }}
@endif

<!-- Firstname -->
@if (($auth_user->hasRole('SuperAdmin')))
<div class="col-sm-12">
<button class="btn btn-primary" id="state_change_button" style="float:right">Change to SSO</button>
</div>
@endif

<div class="form-group">
    {!! Form::label('FieldUserName', trans('users.name').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::text('firstname', null, ['class' => 'form-control', 'id' => 'FieldUserName', 'placeholder' => trans('users.nameRequired')]) !!}
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>

<!-- Lastname -->
<div class="form-group">
    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::text('lastname', null, ['class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>
<div class="form-group">
    {!! Form::label('FieldUserConfirmed',trans('users.confirmed').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4 form-control-static">
        @if($user->confirmed == 0)
            {{trans('users.no')}}
        @else
            {{trans('users.yes')}}
        @endif
    </div>
</div>
<!-- Email -->
<div class="form-group">
    {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4 form-control-static">
        {{ $user->email }}

        @if(($user->hasRole('InstitutionAdministrator') || $user->hasRole('DepartmentAdministrator')))
            <div class="help-block"
                 style="margin:0;">{{trans('users.emailOf')}} {{ trans("users.".$role->name) }}{{trans('users.cannotBeChanged')}}</div>
        @elseif($user->participantInConferences()->count() > 0)
            <div class="help-block" style="margin:0;">{{trans('users.emailFixedJoinedOnce')}}</div>
        @endif

    </div>
</div>

<!-- Telephone -->

<div class="form-group">
    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        @if($user->hasRole('EndUser') == false)
            {!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneRequired')]) !!}
        @else
            {!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
        @endif
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4 form-control-static">
            @if($user->state ==  'local')
                {{trans('users.yes')}}
            @else
                {{trans('users.no')}}
            @endif
        </div>
</div>

<div class="form-group has-success">
    {!! Form::label('FieldUserPassword', trans('users.newPassword').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::checkbox('new_password', 0, true, ['id' => 'FieldUserPassword', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
        <div class="help-block" style="margin:0; color:#737373;">{{trans('users.sendNewPassword')}}.</div>
    </div>
</div>
<div class="form-group has-success">
    {!! Form::label('FieldUserStatus', trans('users.active').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        <?php $value = Form::getValueAttribute('status'); ?>
        {!! Form::checkbox('status', str_is($user->status, 1), null, ['id' => 'FieldUserStatus', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
    </div>
</div>

@if($user->status == 0)
    <div id="FieldUserStatusAlert" class="alert alert-warning" role="alert">
        <span id="FieldUserStatusMessage" style="margin-right:10px">{!!trans('users.activationSelected')!!}: </span>
        {!! Form::checkbox('SendUserEmail', 0, false, ['id' => 'SendUserEmail', 'data-toggle' => 'checkbox-x', 'data-size' => 'sm', 'data-three-state' => 'false']) !!}
        <label class="cbx-label" for="SendUserEmail">{{trans('users.sendPasswordEmail')}}</label>
    </div>
@endif

<!-- Role -->
@if ($auth_user->hasRole('SuperAdmin')  && !$user->hasRole('SuperAdmin'))

    <div class="form-group">
        {!! Form::label('FieldUserRole', trans('users.role').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4">
            {!! Form::select('role', ['' => ''] + App\User::role_dropdown(['SuperAdmin'], 'name'), $user->roles()->first()->name, ['id' => 'FieldUserRole', 'style' => 'width: 100%'])!!}
        </div>
    </div>
    @if($user->HasFutureAdminConferences())
    <div class="form-group">
        <label for="FieldUserRole" class="control-label col-sm-2">Προσοχή:</label>
        <div class="col-sm-4">
            Ο χρήστης έχει διοργανώσει τρέχουσες ή μελλοντικές τηλ/ψεις, εάν τον υποβιβάσετε σε απλό χρήστη οι τηλ/ψεις αυτές θα διαγραφούν.
        </div>
    </div>
    @endif
    <!-- Institution -->
    <div class="form-group">
        {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4">
            @if($institution->slug != 'other')
                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $institution->id, ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
            @else
                {!! Form::select('institution_id', ['' => ''] + App\Institution::whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserOrg', 'style' => 'width: 100%'])!!}
            @endif
        </div>
    </div>

    <div class="form-group" id="NewOrgContainer">
        {!! Form::label('FieldUserOrgNew', trans('users.newInstitution').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4">
            @if($institution->slug == 'other')
            {!! Form::text('new_institution', $user->customValues()['institution'], ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
            @else
            {!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('users.enterInstitutionRequired'), 'id' => 'FieldUserOrgNew']) !!}
            @endif
            <div class="help-block with-errors" style="margin:0;"></div>
        </div>
    </div>
@else
    <div class="form-group">
        {!! Form::label('FieldUseRole', trans('users.role').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4 form-control-static" id="FieldUserRole">
            {{ trans($user->roles()->first()->label) }}
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4 form-control-static">
            {{ $institution->title }} @if($institution->slug == 'other') ({{ $user->customValues()['institution'] }}
            ) @endif
        </div>
    </div>
@endif

<!-- Department -->
<div class="form-group" id="DepContainer">
    {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        @if($department->slug == 'other')
            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], 'other', ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        @else
            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        @endif
    </div>
</div>

<div class="form-group" id="NewDepContainer">
    {!! Form::label('FieldUserDepartNew', trans('users.department').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        @if($department->slug == 'other')
        {!! Form::text('new_department', $user->customValues()['department'], ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
        @else
        {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
        @endif
    </div>
</div>

<!-- Comments -->
<div class="form-group" id="FieldCoordDepartDepartFormGroup">
    {!! Form::label('FieldCoordDepartComment', trans('users.description').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-6">
        {!! Form::textarea('comment', null, ['class' => 'form-control', 'id' => 'FieldCoordDepartComment', 'rows' => '3', 'readonly'])!!}
    </div>
</div>

<!-- Admin Comment -->
<div class="form-group" id="FieldCoordDepartDepartFormGroup">
    {!! Form::label('FieldCoordDepartAdminComment', trans('users.comments').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-6">
        {!! Form::textarea('admin_comment', $user->admin_comment, ['class' => 'form-control', 'placeholder' => trans('users.moderatorComments'), 'rows' => '3', 'id' => 'FieldCoordDepartAdminComment'])!!}
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
		<span class="pull-right">
			<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
				{!! Form::submit(trans('users.saveChanges'), ['class' => 'btn btn-primary', 'name' => 'add_details']) !!}
			</div>
            @if($auth_user->hasRole('SuperAdmin'))
                @if($user->canBeDeleted())
                    <a href="/users/delete/{{ $user->id }}"><button type="button" class="btn btn-danger"
                                                                    id="TeleReturn">{{trans('users.deleteUser')}}</button></a>
                @elseif(!$user->canBeDeleted())
                    <button type="button" class="btn btn-danger" id="TeleReturn" data-toggle="tooltip"
                            data-placement="top"
                            title="{{trans('users.userFixedJoinedOnce')}}">{{trans('users.deleteUser')}}</button>
                @endif
            @endif
            <a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default"
                                                                 id="TeleReturn">{{trans('users.return')}}</button></a>
		</span>
    </div>
</div>