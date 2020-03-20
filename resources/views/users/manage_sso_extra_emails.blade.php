@extends('app')

@section('header-javascript')    <!-- checkbox -->




@endsection

@section('extra-css')

    <style>
        .container {
            min-width: 400px !important;
        }

        .box-padding {
            padding: 20px 30px;
        }

    </style>
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

@endsection

@section('account-active')
    class = "active"
@endsection

@section('content')

    <section id="OLogarismosMou">
        <div class="container">
            <div class="box box-padding" style="margin-top:100px; overflow:auto;">
                <div class="row" style="margin-bottom:20px;">
                    <div class="col-xs-7">
                        <h4 style="color:#52B6EC">{{trans('users.email_management')}}</h4>
                    </div>
                </div>
                <div class="well well-sm" style="overflow:auto;">
                    <div class="col-sm-12" style="margin-bottom:20px;">
                        <div class="form-group">
                            {!! Form::label('FieldUserEmail',trans('users.primaryEmail').':', ['class' => 'control-label col-sm-2']) !!}
                            <div class="col-sm-10">
                                {!! Form::text('primary_email', $primary_email, ['class' => 'form-control','readonly'=>'true','style'=>'border-color:green; margin-bottom:5px;']) !!}
                                <div class="alert alert-warning help-block"
                                     role="alert">{!!trans('users.ExtraEmailsWarningPersonal')!!}</div>
                            </div>
                        </div>
                        <div class="form-group">
                            {!! Form::label('FiedUserEmail',trans('users.extraEmail').':', ['class' => 'control-label col-sm-12']) !!}
                        </div>
                        @foreach($extra_emails['sso'] as $mail)
                            <div class="col-sm-12">
                                <div style="color:green;" class="col-sm-2">
                                    ({{trans('users.emailConfirmedShort')}})
                                </div>
                                <div class="col-sm-6">
                                    {!! Form::text('primary_email', $mail['email'], ['class' => 'form-control','readonly'=>'true','style'=>'border-color:green; margin-bottom:5px;']) !!}
                                </div>
                                <div class="col-sm-4">
                                    <div class="btn-group">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary"
                                                    onclick="makePrimary({{$mail['id']}})">{!! trans('users.make_primary') !!}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        @foreach($extra_emails['custom'] as $mail)
                            @if($mail['confirmed'] == 0)
                                {{--<div style="color:red;">--}}
                                {{--{{$mail['email']}}--}}
                                {{--({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})--}}
                                {{--</div>--}}
                                <div class="col-sm-12">
                                    <div style="color:red;" class="col-sm-2">
                                        ({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})
                                    </div>
                                    <div class="col-sm-6">
                                        {!! Form::text('primary_email', $mail['email'], ['class' => 'form-control','readonly'=>'true','style'=>'border-color:red; margin-bottom:5px;']) !!}
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" style="margin-right:12px;"
                                                    onclick="resendActivationLink({{$mail['id']}})">{{trans('account.extra_email_resend_link')}}</button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteExtraMail({{$mail['id']}})">{!! trans('conferences.delete') !!}</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-sm-12">
                                    <div style="color:green;" class="col-sm-2">
                                        ({{trans('users.customExtraMail')}} {{trans('users.emailConfirmedShort')}})
                                    </div>
                                    <div class="col-sm-6">
                                        {!! Form::text('primary_email', $mail['email'], ['class' => 'form-control','readonly'=>'true','style'=>'border-color:green; margin-bottom:5px;']) !!}
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-primary" style="margin-right:12px;"
                                                    onclick="makePrimary({{$mail['id']}})">{!! trans('users.make_primary') !!}</button>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteExtraMail({{$mail['id']}})">{!! trans('conferences.delete') !!}</button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="alert alert-warning help-block col-sm-12" role="alert">
                        <div class="col-sm-12">
                            <p><strong>{!! trans('users.slotsavailable') !!}:</strong> <span
                                        id="availableSlots">{!!$slots_remaining!!}</span>
                            </p>
                        </div>

                        @if(session('message'))
                            <div class="col-sm-12">
                                <div class="alert alert-success" style="border:none;">
                                    {!! session('message') !!}
                                </div>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="col-sm-12">
                                <div class="alert alert-danger" style="border:none;">
                                    <ul>
                                        <strong>{{trans('users.changesNotSaved')}}</strong>
                                        <li>{{session('error')}}</li>
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if(session()->has('status') && session()->get('status') == "success")
                            <div class="col-sm-12">
                                <div class="alert alert-success" style="border:none;">
                                    <strong>{!! trans('account.extra_email_confirmation_email_sent') !!}</strong>
                                </div>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="col-sm-12">
                                <div class="alert alert-danger" style="border:none;">
                                    <ul style="margin: 0px 15px 10px 15px">
                                        <strong>{{trans('site.changesNotSaved')}}</strong>
                                        @foreach($errors->all() as $error)
                                            <li>{!! $error !!}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @if($slots_remaining>0)
                            {!! Form::open(array('url' => ['/users/'.$user->id.'/emails/add_new'], 'method' => 'POST', 'class' => 'form-horizontal')) !!}
                            <div class="form-group">
                                <div class="col-sm-8">
                                    {!! Form::text('new_extra_email', null, ['placeholder'=>trans('conferences.adduser').' '.trans('users.extraEmail'),'class' => 'form-control']) !!}
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-4">
                                    {!! Form::submit(trans('conferences.adduser'), ['class' => 'btn btn-primary']) !!}
                                </div>
                            </div>
                            {!! Form::close() !!}
                        @endif
                    </div>
                    <div class="col-sm-12">
                        <a href="/users/{{$user->id}}/edit">
                            <button style="float:right;" type="button"
                                    class="btn btn-sm btn-primary">{!! trans('users.return') !!}</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
<script>


    function resendActivationLink(id){

        var r = confirm('{!! trans('account.extra_email_resend_link_confirmation') !!}');
        if (r === true) {

            $.post("/users/{{$user->id}}/emails/resend_extra_email_confirmation", {
                _token: "{!!csrf_token()!!}",
                id: parseInt(id)
            })
                .done(function (data) {

                    var response = JSON.parse(data);
                    if (response.status === 'success') {
                        alert(response.message);
                        // window.location = '/account/emails';
                    }

                })
                .fail(function (error) {
                    console.log(error.responseText);
                })
        } else {
            console.log("You pressed Cancel!");
        }

    }


    function deleteExtraMail(id) {
        var r = confirm('{!! trans('users.extraEmailDelete_confirmation') !!}');
        if (r === true) {
            $.post("/users/{{$user->id}}/emails/deleteExtraEmail", {
                _token: "{!!csrf_token()!!}",
                id: parseInt(id)
            })
                .done(function (data) {
                    console.log(data);
                    var response = JSON.parse(data);
                    if (response.status === 'success') {
                        window.location = '';
                    }
                })
                .fail(function (error) {
                    console.log(error.responseText);
                })
        } else {
            console.log("You pressed Cancel!");
        }
    }

    function makePrimary(id) {
        var r = confirm('{!! trans('users.extraEmailPrimary_confirmation') !!}');
        if (r === true) {
            $.post("/users/{{$user->id}}/emails/makePrimary", {
                _token: "{!!csrf_token()!!}",
                id: parseInt(id)
            })
                .done(function (data) {
                    console.log(data);
                    var response = JSON.parse(data);
                    if (response.status === 'success') {
                        window.location = '';
                    }
                })
                .fail(function (error) {
                    console.log(error.responseText);
                })
        } else {
            console.log("You pressed Cancel!");
        }
    }


</script>
