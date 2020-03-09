@if (session('message'))
    <p class="alert alert-info" style="margin: 0px 15px 10px 15px">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {!! session('message') !!}
    </p>
@endif
@if (session('error'))
    <p class="alert alert-danger" style="margin: 0px 15px 10px 15px">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        {!! session('error') !!}
    </p>
@endif
<div class="alert alert-info">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    {{trans('site.confirmationMessage')}}
</div>
{!! Form::model($user, array('url' => ['/send_email_confirmation_link'], 'method' => 'POST', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}
{{--Pending email--}}
<div class="form-group">
    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::text('lastname', $user->lastname, ['readonly'=>'true','class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
    </div>
</div>

<div class="form-group">
    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::text('firstname', $user->firstname, ['readonly'=>'true','class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
        <div class="help-block with-errors" style="margin:0px;"></div>
    </div>
</div>
<div class="form-group">
    {!! Form::label('FieldUserPrimaryEmail', trans('users.primaryEmail'), ['class' => 'control-label col-sm-2']) !!}
    <div class="col-sm-4">
        {!! Form::text('email', $user->email,['id' => 'FieldUserEmailPending','aria-describedby' => 'helpBlockRole','class' => 'form-control'])!!}
        <div class="help-block with-errors" style="margin:0;"></div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
		<span class="pull-right">
				<div class="btn-group" role="group" id="TeleInitialSaveGroupButtons">
                     {!! Form::submit(trans('site.confirmationNewEmail'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'UserSubmitBtnNew']) !!}
                </div>
		</span>
    </div>
</div>
{!! Form::close() !!}