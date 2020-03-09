@extends('app')
@section('header-javascript')
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">
    <!-- checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">
    <!-- bootstrap text editor       -->
    <link href="/summernote/summernote.css" rel="stylesheet">
    <script src="/summernote/summernote.min.js"></script>
    <script src="/summernote/summernote-el-GR.js"></script>
    <link rel="stylesheet" href="/css/font-awesome.css">
    <!-- bootstrap date-picker    -->
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">
    <!-- bootstrap clock-picker    -->
    <script type="text/javascript" src="/clock-picker/clockpicker.js"></script>
    <link href="/clock-picker/clockpicker.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">
    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <script type="text/javascript">
        $(document).ready(function () {
            $('.summernote').summernote({
                lang: 'el-GR'
            });

            $('[data-toggle="tooltip"]').tooltip();

            $("#UserOrgNew").hide();
            $("#UserDepartNew").hide();
            $("#FieldUserDepart").select2({
                placeholder: "{!!trans('users.selectDepartment')!!}",
                allowClear: true
            });
            $("#FieldUserEmailSelect").select2();

            //Load departments for Φυσικό πρόσωπο
            $("#FieldUserOrg").select2({
                allowClear: true,
                placeholder: "{!!trans('users.selectInstitutionOptional')!!}"
            }).on("change", function () {
                if ($("#FieldUserOrg").val() == "other") {
                    $("#UserOrgNew").show();
                    $("#UserDepartNew").show();
                    $("#FieldUserDepart").val(0);
                    $("#UserDepart").hide();
                } else if ($("#FieldUserOrg").val() != "other" && $("#FieldUserOrg").val() > 0) {
                    $("#FieldUserDepart").select2("data", null, {allowClear: true}).load("/institutions/departments/" + $("#FieldUserOrg").val());
                    $("#UserOrgNew").hide();
                    $("#UserDepartNew").hide();
                    $("#UserDepart").show();
                }
                else if ($("#FieldUserOrg").val() == "") {
                    $("#UserOrgNew").hide();
                    $("#UserDepartNew").hide();
                    $("#UserDepart").show();
                    $("#FieldUserDepart").select2({
                        placeholder: "{!!trans('users.selectDepartment')!!}",
                        allowClear: true
                    });
                }
            }).trigger("change");

            $("#FieldUserDepart").on("change", function () {
                if ($("#FieldUserDepart").val() == "other") {
                    $("#UserDepartNew").show();
                } else if ($("#FieldUserDepart").val() != "other" && $("#FieldUserDepart").val() > 0) {
                    $("#UserDepartNew").hide();
                }
                else if ($("#FieldUserDepart").val() == "") {
                    $("#UserDepartNew").hide();
                }
            }).trigger("change");

            $("#sendConfirmationEmailSSO").on("click", function () {
                var email = $("#FieldUserEmailSelect option:selected").attr("value");
                $.post("/new_sso_account/sendConfirmationEmailSSO", {email: email})
                        .done(function (data) {
                            alert(data);
                        });
            });

        });
    </script>
@endsection
@section('extra-css')
    <style>
        .container {
            min-width: 550px !important;
        }
        .zero-width {
            display: none;
            width: 0px;
        }
        table#example td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }
        .cellPName {
            width: 300px !important;
        }
        .cellPRole {
            width: 120px !important;
        }
        .cellPEmail {
            width: 210px !important;
        }
        .cellPType {
            width: 100px !important;
        }
        .cellPStatus {
            width: 110px !important;
        }
        .cellPSendEmail {
            width: 85px !important;
        }
        .cellPConfirm {
            width: 85px !important;
        }
        .cellPButton {
            padding: 3px !important;
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }
        tfoot {
            display: table-header-group;
        }
        .datepicker {
            padding: 0px;
        }
        /* CLASSES FOR USERS DATATABLE START */
        table#UsersExample td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }
        .cellName {
            width: 255px !important;
        }
        .cellRole {
            width: 80px !important;
        }
        .cellEmail {
            width: 125px !important;
        }
        .cellOrg {
            width: 170px !important;
        }
        .cellDepart {
            width: 120px !important;
        }
        .cellType {
            width: 70px !important;
        }
        .cellStatus {
            width: 80px !important;
        }
        .cellButton {
            padding: 3px !important;
            width: 50px !important;
            min-width: 50px !important;
            max-width: 50px !important;
        }
        .ssoRow {
            display: table;
            width: 100%;
        }
        .ssoColumn {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
    </style>
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <h4></h4>
                <hr/>
                <div class="alert alert-warning">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{trans('site.activationText')}}
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
                {!! Form::open(array('url' => 'store_new_sso_user', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'UserForm', 'role' => 'form')) !!}
                {!! Honeypot::generate('my_name', 'my_time') !!}
                <div class="form-group">
                    {!! Form::label('FieldUserSurname', trans('site.lastName').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('lastname', isset($lastname) ? $lastname : null, ['class' => 'form-control', 'placeholder' => trans('site.lastNameRequired'), 'id' => 'FieldUserSurname']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserΝame', trans('site.firstName').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('firstname', isset($name) ? $name : null, ['class' => 'form-control', 'placeholder' => trans('site.firstNameRequired'), 'id' => 'FieldUserΝame']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                @if(empty($emails))
                    <div class="form-group">
                        {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-2 ']) !!}
                        <div class="col-sm-4">
                            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => trans('site.emailRequired'), 'id' => 'FieldUserEmail']) !!}
                            <div class="help-block with-errors" style="margin:0px;"></div>
                        </div>
                    </div>
                    {!! Form::hidden('custom_email_flag') !!}
                @else
                    @foreach($emails as $k=>$email)
                        @if($k==0)
                            <div class="form-group">
                                {!! Form::label('FieldUserPrimaryEmail', trans('users.primaryEmail'), ['class' => 'control-label col-sm-2 ']) !!}
                                <span class="glyphicon glyphicon-info-sign"
                                      title="{!!  trans('users.primaryEmailMessage') !!}"></span>
                                <div class="col-sm-4">
                                    {!! Form::text('email', $email,['id' => 'FieldUserEmail-'.$k, 'style' => 'width: 100%; background-color : #d1d1d1; ', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                                    <div class="help-block with-errors" style="margin:0px;"></div>
                                </div>
                            </div>
                        @else
                            <div class="form-group">
                                {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmail').' '.$k, ['class' => 'control-label col-sm-2 ']) !!}
                                <span class="glyphicon glyphicon-info-sign"
                                      title="{!!  trans('users.extraEmailMessage') !!}"></span>
                                <div class="col-sm-4">
                                    {!! Form::text('extra_sso_email_'.$k, $email,['id' => 'FieldUserEmail-'.$k, 'style' => 'width: 100%; background-color : #d1d1d1;', 'aria-describedby' => 'helpBlockRole', 'readonly'=>'true'])!!}
                                    <div class="help-block with-errors" style="margin:0px;"></div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if(count($emails)<=3)

                            <div class="form-group">

                                {!! Form::label('FieldUserExtraEmail-'.$k, trans('users.extraEmailsLeft').(4-count($emails)), ['class' => 'control-label col-sm-2 ']) !!}
                                <div class="col-sm-4">
                                    {!!trans('users.userNotCreatedYet')!!}
                                {{--<button class="btn btn-primary" id="addAdditionEmailButton"><span class="glyphicon glyphicon-plus"></span> {!! trans('conferences.adduser').' '.trans('users.extraEmail') !!} </button>--}}
                                </div>
                            </div>

                    @endif
                @endif
                {!! Form::hidden('persistent_id', $persistent_id) !!}
                <div class="form-group">
                    {!! Form::label('FieldUserPhone', trans('site.phone').':', ['class' => 'control-label col-sm-2']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('telephone', isset($telephone) ? $telephone : null, ['class' => 'form-control', 'placeholder' => trans('site.phoneOptional'), 'id' => 'FieldUserPhone']) !!}
                        <div class="help-block with-errors" style="margin:0px;"></div>
                    </div>
                </div>

                <div class="form-group">
                    {!! Form::label('FieldUserOrg', trans('site.institution').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4 form-control-static">
                        {{ $institution->title }}
                        <div class="help-block with-errors" style="margin:0;"></div>
                    </div>
                </div>

                {!! Form::hidden('institution_id', $institution->id,['id'=>'InstitutionId']) !!}

                <div class="form-group" id="FieldUserDepartFormGroup">
                    {!! Form::label('FieldUserDepart', trans('site.department').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::select('department_id', ['' => ''] + App\Department::where('institution_id', $institution->id)->whereNotIn('slug', ['other'])->orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('site.other')], null, ['id' => 'FieldUserDepart', 'style' => 'width: 100%'])!!}
                    </div>
                </div>

                <div class="form-group" id="UserDepartNew">
                    {!! Form::label('FieldUseDepartNew', trans('site.newDepartment').':', ['class' => 'control-label col-sm-2 ']) !!}
                    <div class="col-sm-4">
                        {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('site.enterDepartment'), 'id' => 'FieldUseDepartNew']) !!}
                        <div id="newDepDiv" class="help-block with-errors newdep alert alert-warning"
                             style="margin:0;">{{ trans('users.newDeptWarning') }}</div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Terms" class="control-label col-sm-2">{{trans('site.termsAcceptance')}} : </label>
                    <div class="col-sm-4 form-control-static">
                        <input name="accept_terms_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('accept_terms_input') =='on')) checked @endif>
                        <a href="/terms" target="_blank"> {{trans('site.termsSite')}}</a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="Privacy" class="control-label col-sm-2">{{trans('site.privacyPolicyAcceptance')}} : </label>
                    <div class="col-sm-4 form-control-static">
                        <input name="privacy_policy_input" type="checkbox" @if(!empty($user->accepted_terms) || (old('privacy_policy_input') =='on')) checked @endif>
                        <a href="/privacy_policy" target="_blank"> {{trans('site.privacy_policy')}}</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12">
												<span class="pull-right">   
													<div class="btn-group" role="group"
                                                         id="TeleInitialSaveGroupButtons">
                                                        {!! Form::submit(trans('site.save'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'UserSubmitBtnNew']) !!}
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
