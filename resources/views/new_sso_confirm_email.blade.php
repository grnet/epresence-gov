@extends('app')
@section('header-javascript')
    <link rel="stylesheet" href="/select2/select2-small.css">
    <!-- checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">
    <link rel="stylesheet" href="/css/font-awesome.css">
    <link href="/css/main.css" rel="stylesheet">
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <h4></h4>
                <hr/>
                <div class="alert alert-warning">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{trans('site.activateEmailText')}}
                </div>

                @if ($errors->any())
                    <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                        <strong>{{trans('site.changesNotSaved')}}</strong>
                        @foreach($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                @elseif (session('message'))
                    <div class="alert alert-info">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ session('message') }}
                    </div>
                @endif
                {!! Form::open(array('url' => 'send_email_confirmation_link_create_user', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'UserForm', 'role' => 'form')) !!}
                {!! Honeypot::generate('my_name', 'my_time') !!}
                <div class="form-group">
                    {!! Form::label('FieldUserSurname', trans('site.lastName').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('lastname', isset($lastname) ? $lastname : null, ['readOnly'=>true,'class' => 'form-control', 'placeholder' => trans('site.lastNameRequired'), 'id' => 'FieldUserSurname']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserΝame', trans('site.firstName').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('firstname', isset($name) ? $name : null, ['readOnly'=>true,'class' => 'form-control', 'placeholder' => trans('site.firstNameRequired'), 'id' => 'FieldUserΝame']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => trans('site.emailRequired'), 'id' => 'FieldUserEmail']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                {!! Form::hidden('persistent_id', $persistent_id) !!}
                {!! Form::hidden('institution_id', $institution->id) !!}
                {!! Form::hidden('telephone', $telephone) !!}

                <div class="row">
                    <div class="col-sm-12">
		    				<span class="pull-right">
							<div class="btn-group" role="group"
                               id="TeleInitialSaveGroupButtons">
                              {!! Form::submit(trans('site.confirmationEmail'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'UserSubmitBtnNew']) !!}
                            </div>
	    				</span>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        <!--/.box-->
        <!-- Form Details -END -->
    </section>
@endsection
