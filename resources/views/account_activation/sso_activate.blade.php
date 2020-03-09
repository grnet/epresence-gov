{!! Form::model($user, array('url' => ['/account_activation_'.$user->state], 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}

<div class="form-group">
    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8">
        {!! Form::text('lastname', $user->lastname, ['class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8">
        {!! Form::text('firstname', $user->firstname, ['class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>
@if(isset($invited_email_key))
    <input type="hidden" name="invited_email_key" value="{{$invited_email_key}}">
@endif
@foreach($emails as $k=>$email)
    @if($k==0)
        <div class="form-group">
            {!! Form::label('FieldUserPrimaryEmail', trans('users.primaryEmail'), ['class' => 'control-label col-sm-4 ']) !!}
            <span class="glyphicon glyphicon-info-sign"
                  title="{!!  trans('users.primaryEmailMessage') !!}"></span>

            <div class="col-sm-8">
                {!! Form::text('email', $email,['id' => 'FieldUserEmail-'.$k,'class' => 'form-control','style' => 'width:100%; background-color : #d1d1d1; ', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                <div class="help-block with-errors" style="margin:0;"></div>
            </div>
        </div>
    @else
        <div class="form-group">
            {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmail').' '.$k, ['class' => 'control-label col-sm-4 ']) !!}
            <span class="glyphicon glyphicon-info-sign"
                  title="{!!  trans('users.extraEmailMessage') !!}"></span>

            <div class="col-sm-8">
                {!! Form::text('extra_sso_email_'.$k, $email,['id' => 'FieldUserEmail-'.$k,'class' => 'form-control','style' => 'width: 100%; background-color : #d1d1d1;', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                <div class="help-block with-errors" style="margin:0px;"></div>
            </div>
        </div>
    @endif
@endforeach
@if(count($emails)<=3)
    <div class="form-group">
        {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmailsLeft').(4-count($emails)), ['class' => 'control-label col-sm-4 ']) !!}
        <div class="col-sm-8">
            {!!trans('users.userNotCreatedYet')!!}
        </div>
    </div>
@endif
<div class="form-group">
    {!! Form::label('FieldUseStatus', trans('users.localUserShort').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8 form-control-static">
        {{ $user->state_string($user->state) }}
    </div>
</div>
<div class="form-group">
    {!! Form::label('FieldUserPhone', trans('users.telephone').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8">
        {!! Form::text('telephone',$user->telephone, ['class' => 'form-control', 'id' => 'FieldUserPhone', 'placeholder' => trans('users.telephoneOptional')]) !!}
        <div class="help-block with-errors" style="margin:0px;"></div>
    </div>
</div>
<div class="form-group">
    {!! Form::label('FieldUserImage', trans('users.uploadPhoto').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8 form-control-static">
        @if(!empty($user->thumbnail))
            <div class="card">
                <img src="/images/user_images/{{ $user->thumbnail }}" class="card-img-top img-thumbnail"
                     alt="Responsive image">

                <div class="card-block">
                    <a href="#" id="deleteMyUserImage"
                       class="card-link">{{trans('users.deletePhoto')}}</a>
                </div>
                <div class="small-gap"></div>
            </div>
        @endif
        {!! Form::file('thumbnail', ['id' => 'FieldUserImage']) !!}
        <p class="help-block">{{trans('users.acceptedFileTypes')}}: jpeg, png, bmp, gif,
            svg. {{trans('users.maxFileSize')}}: 300kB.</p>
    </div>
</div>
<div class="form-group">
    {!! Form::label('FieldUseRole', trans('users.role').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8 form-control-static" id="FieldUserRole">
        {{ trans($role->label) }}
    </div>
</div>
{{--Basic info section end--}}

{{--Institution section start--}}

<div class="form-group">
    {!! Form::label('FieldUserOrg', trans('users.institution').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8 form-control-static">
        @if($institution->slug == 'other')
            {{ $institution->title }} ({{ $user->customValues()['institution'] }})
        @else
            {{ $institution->title }}
        @endif
    </div>
</div>
{{--Institution section end--}}
{{--Department section start--}}


<div class="form-group" id="DepContainer">
    {!! Form::label('FieldUserDepart', trans('users.department').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8">
        @if($department && $department->slug == "other")
        {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')],'other', ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        @elseif($department && $department->slug != "other")
            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')],$department->id , ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        @else
            {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('users.other')],null, ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
        @endif
    </div>
</div>
<div class="form-group" id="NewDepContainer">
    {!! Form::label('FieldUserDepartNew', trans('users.newDepartment').':', ['class' => 'control-label col-sm-4']) !!}
    <div class="col-sm-8">
        {!! Form::text('new_department', $user->customValues()['department'], ['class' => 'form-control', 'placeholder' => trans('users.enterDepartmentRequired'), 'id' => 'FieldUserDepartNew']) !!}
        <div id="newDepDiv" class="help-block with-errors newdep alert alert-warning"
             style="margin:0;">{{ trans('users.newDeptWarning') }}</div>
    </div>
</div>

<div class="form-group">
    <label for="Terms" class="control-label col-sm-4">{{trans('site.termsAcceptance')}} : </label>
    <div class="col-sm-8 form-control-static">
        <input name="accept_terms_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('accept_terms_input') =='on')) checked @endif>
        <a href="/terms" target="_blank"> {{trans('site.termsSite')}}</a>
    </div>
</div>
<div class="form-group">
    <label for="Privacy" class="control-label col-sm-4">{{trans('site.privacyPolicyAcceptance')}} : </label>
    <div class="col-sm-8 form-control-static">
        <input name="privacy_policy_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('privacy_policy_input') =='on')) checked @endif>
        <a href="/privacy_policy" target="_blank"> {{trans('site.privacy_policy')}}</a>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
		<span class="pull-right">
				<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
                      {!! Form::submit(trans('site.accountActivation'), ['class' => 'btn btn-primary']) !!}
                </div>
		</span>
    </div>
</div>
{!! Form::close() !!}