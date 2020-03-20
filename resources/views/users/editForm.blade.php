@if(is_null(Session::get('previous_url')))
    {{ Session::put('previous_url', URL::previous()) }}
@endif

<!-- Firstname -->
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
        <div class="help-block" style="margin:0;">{{trans('users.emailFixedSso')}}</div>
    </div>
</div>
<div id="xtraMailsList">
    @foreach($extra_emails['sso'] as $mail)
        <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
            {!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-2']) !!}
            <div class="col-sm-4" style="color:green; padding-top:7px;">
                {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
            </div>
        </div>
    @endforeach
    @foreach($extra_emails['custom'] as $mail)
        <div class="form-group" id="formGroup_{!! $mail['id'] !!}">
            {!! Form::label('FieldExtraEmail'.$mail['id'], trans('users.extraEmail').':', ['class' => 'control-label col-sm-2']) !!}

            @if($mail['confirmed'] == 1)
                <div class="col-sm-4" style="color:green; padding-top:7px;">
                    {{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailConfirmedShort')}})
                </div>
            @else
                <div class="col-sm-4" style="color:red; padding-top:7px;">
                    {{$mail['email']}} ({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})
                </div>
            @endif
        </div>
    @endforeach
</div>
@if($auth_user->hasRole('SuperAdmin'))
<div class="col-sm-12" style="margin-top:20px; margin-bottom:20px;">
    <div class="col-sm-4 col-sm-push-2" style="padding:0; margin:0;">
        <a href="/users/{{$user->id}}/edit/emails">{{trans('users.email_management')}}</a>
    </div>
</div>
@endif
<!-- Telephone -->
<div class="form-group">
    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
            {!! Form::text('telephone', null, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephone')]) !!}
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>
<div class="form-group has-success">
    {!! Form::label('FieldUserStatus', trans('users.active').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-8">
        <?php $value = Form::getValueAttribute('status'); ?>
        {!! Form::checkbox('status', str_is($user->status, 1), null, ['id' => 'FieldUserStatus', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
    </div>
</div>
<!-- Role -->

@if(($auth_user->hasRole('SuperAdmin')) && !$user->hasRole('SuperAdmin'))
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
@else
    <div class="form-group">
        {!! Form::label('FieldUseRole', trans('users.role').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4 form-control-static" id="FieldUserRole">
            {{ trans($user->roles()->first()->label) }}
        </div>
    </div>
@endif
<div class="form-group">
    {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-2']) !!}
    @if($auth_user->hasRole('SuperAdmin'))
        <div class="col-sm-4">
        {!! Form::select('institution_id', ['' => ''] + App\Institution::pluck("title","id")->toArray(), $institution->id, ['id' => 'FieldUserOrg', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
        </div>
    @else
    <div class="col-sm-4 form-control-static">
            {{ $institution->title }}
        <input type="hidden" name="institution_id" value="$institution->id">
    </div>
     @endif
</div>
    <div class="form-group">
        {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4">
                {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', old('institution_id',$institution->id))->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')], $department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        </div>
    </div>
    <div class="form-group" id="NewDepContainer">
        {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-2']) !!}
        <div class="col-sm-4">
                {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
                <div id="newDepDiv" class="help-block with-errors newdep alert alert-warning"
                     style="margin:0;">{{ trans('users.newDeptWarning') }}</div>
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
        {!! Form::textarea('admin_comment', null, ['class' => 'form-control', 'placeholder' => trans('users.moderatorComments'), 'rows' => '3', 'id' => 'FieldCoordDepartAdminComment'])!!}
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