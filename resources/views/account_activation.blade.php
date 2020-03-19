@extends('app')
@section('header-javascript')
    <link href="select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="select2/select2.js"></script>
    <script type="text/javascript" src="select2/select2_locale_el.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            let selectInstitutionField = $("#FieldSelectInstitutionId");
            selectInstitutionField.select2({
                allowClear: true,
                placeholder: "{!!trans('users.selectInstitutionRequired')!!}"
            }).on("change", function () {
            });
         });
    </script>
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px;">
                <h4>{{trans('site.activationForUser')}}: {{ $user->firstname }} {{ $user->lastname }}</h4>
                <hr/>
                <div class="alert alert-warning">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{trans('site.activationText')}}
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            <strong>{{trans('users.changesNotSaved')}}</strong>
                            @foreach($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('message'))
                    <div class="alert alert-info">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ session('message') }}
                    </div>
                @endif
                {!! Form::model($user, array('method' => 'POST', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form', 'files' => true)) !!}
                <div class="form-group">
                    {!! Form::label('FieldUserSurname', trans('users.surname').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('lastname', $user->lastname, ['disabled'=>true,'class' => 'form-control','id' => 'FieldUserSurname', 'placeholder' => trans('users.surnameRequired')]) !!}
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::label('FieldUserΝame', trans('users.name').':', ['class' => 'control-label col-sm-4']) !!}
                    <div class="col-sm-8">
                        {!! Form::text('firstname', $user->firstname, ['disabled'=>true,'class' => 'form-control', 'id' => 'FieldUserΝame', 'placeholder' => trans('users.nameRequired')]) !!}
                        <div class="help-block with-errors" style="margin:0;"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="emailInput" class="control-label col-sm-4">{{trans('users.primaryEmail')}} <span
                                class="glyphicon glyphicon-info-sign"
                                title="{!!  trans('users.primaryEmailMessage') !!}"></span></label>
                    <div class="col-sm-8">
                        <input type="email" name="email" @if(!empty($user->email_verified_at)) disabled @endif
                               value="{{old('email',$user->hasEmailAddress() ? $user->email : null)}}"
                               class="form-control" aria-describedby="helpBlockRole" id="emailInput"
                               placeholder="{{trans('users.primaryEmail')}}">
                        @if(empty($user->email_verified_at))
                        @if(!$user->hasEmailAddress())
                            <div class="help-block with-errors" style="margin:0;">{{trans('account.please_add_email')}}
                            </div>
                        @else
                            <div class="help-block with-errors"
                                 style="margin:0;">{{trans('account.please_confirm_email')}}
                            </div>
                        @endif
                        @else
                            <div class="help-block with-errors"
                                 style="margin:0;">{{trans('users.emailConfirmed')}}
                            </div>
                        @endif
                    </div>
                </div>
                {{--Basic info section end--}}
                {{--Institution section start--}}
                <div class="form-group">
                    {!! Form::label('FieldCoordDepartOrg', trans('site.institution').':', ['class' => 'control-label col-sm-4 ']) !!}
                    <div class="col-sm-8 form-control-static">
                            {!! Form::select('institution_id',$institutionOptions, $institution->id, ['id' => 'FieldSelectInstitutionId', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                        <div class="help-block with-errors" style="margin:0;"></div>
                    </div>
                </div>
                {{--Institution section end--}}
                <div class="form-group">
                    <label for="Terms" class="control-label col-sm-4">{{trans('site.termsAcceptance')}} : </label>
                    <div class="col-sm-8 form-control-static">
                        <input name="accept_terms_input" type="checkbox" id="Terms"
                               @if(!empty($user->accepted_terms) || (old('accept_terms_input') =='on')) checked @endif>
                        <a href="/terms" target="_blank"> {{trans('site.termsSite')}}</a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Privacy" class="control-label col-sm-4">{{trans('site.privacyPolicyAcceptance')}}
                        : </label>
                    <div class="col-sm-8 form-control-static">
                        <input name="privacy_policy_input" id="Privacy" type="checkbox"
                               @if(!empty($user->accepted_terms) || (old('privacy_policy_input') =='on')) checked @endif>
                        <a href="/privacy_policy" target="_blank"> {{trans('site.privacy_policy')}}</a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="btn-group pull-right" role="group" id="TeleInitialSaveGroupButtons">
                            @if(empty($user->email_verified_at))
                            {!! Form::submit(trans('site.confirmationEmail'), ['class' => 'btn btn-primary']) !!}
                            @else
                            {!! Form::submit(trans('site.accountActivation'), ['class' => 'btn btn-primary']) !!}
                            @endif
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div><!--/.box-->
    </section>
    <!-- Form Details -END -->
@endsection
