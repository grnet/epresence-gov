@extends('app')

@section('header-javascript')

    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

    <!-- checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">

    <link rel="stylesheet" href="/css/font-awesome.css">

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">

    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">

    <script src="/js/carousel.js"></script>
    <link rel="stylesheet" href="/css/carousel.css">

    @if ($errors->any() && session('showInitialForm') == false)
        <script type="text/javascript">
            $(document).ready(function () {

                $("#selectAuthentication").hide();
                $("#applicationForm").show();

            });
        </script>
    @elseif ($errors->any() && session('showInitialForm'))
        <script type="text/javascript">
            $(document).ready(function () {

                $("#selectAuthentication").show();
                $("#applicationForm").hide();

            });
        </script>

    @elseif (!empty($persistent_id) && session('showInitialForm') == false)
        <script type="text/javascript">
            $(document).ready(function () {

                $("#selectAuthentication").hide();
                $("#applicationForm").show();
                $("#CoordDepart").modal("show");

            });
        </script>

    @else
        <script type="text/javascript">
            $(document).ready(function () {

                $("#selectAuthentication").show();
                $("#applicationForm").hide();

            });
        </script>
    @endif

    <script type="text/javascript">
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();

            $('[data-toggle="popover"]').popover();


            $('.tt_large').tooltip({
                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner large"></div></div>'
            });

            $("#localApplication").click(function () {
                $("#selectAuthentication").slideUp();
                $("#applicationForm").show();
            });

            // ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΟΝ ΣΥΝΤΟΝΙΣΤΗ ΤΜΗΜΑΤΟΣ

            $("#FieldCoordDepartRole").select2({placeholder: "{!!trans('site.selectRoleRequired')!!}"});

            $("#FieldCoordDepartDepart").select2();

            $("#FieldCoordDepartOrg").select2({
                placeholder: "{!!trans('site.selectInstitution')!!}"
            }).on("change", function () {

                if ($("#FieldCoordDepartOrg").val() == "other") {
                    $("#CoordDepartOrgNew").show();


                    if ($("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {
                        $("#CoordDepartDepartNew").show();
                        $("#FieldCoordDepartDepartFormGroup").hide();

                        $("#FieldCoordDepartDepart").select2().load("/institutions/departments/other", function () {
                            $("#FieldCoordDepartDepart").val("other");
                        });

                    }
                }
                else if ($("#FieldCoordDepartOrg").val() > 0 && $("#FieldCoordDepartOrg").val() !== "other") {
                    $("#FieldCoordDepartDepart").select2("data", null).load("/institutions/departments/" + $("#FieldCoordDepartOrg").val());
                    $("#CoordDepartOrgNew").hide();
                    if ($("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {
                        $("#CoordDepartDepartNew").hide();
                        $("#FieldCoordDepartDepartFormGroup").show();
                    }
                }
                else {
                    $("#FieldCoordDepartDepart").select2({placeholder: "{!!trans('site.selectInstitution')!!}"});
                    $("#CoordDepartOrgNew").hide();
                    if ($("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {
                        $("#CoordDepartDepartNew").hide();
                        $("#FieldCoordDepartDepartFormGroup").show();
                    }
                }
            }).trigger("change");

            $("#FieldCoordDepartDepart").on("change", function () {

                if ($("#FieldCoordDepartDepart").val() == "other") {
                    $("#CoordDepartDepartNew").show();
                } else if ($("#FieldCoordDepartDepart").val() != "other" && $("#FieldCoordDepartDepart").val() > 0) {
                    $("#CoordDepartDepartNew").hide();
                }
                else if ($("#FieldCoordDepartDepart").val() == "") {
                    $("#CoordDepartDepartNew").hide();
                }
            }).trigger("change");

            $("#FieldCoordDepartRole").on("change", function () {
                if ($("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {

                    $("#FieldCoordDepartDepartFormGroup").show();
                    if ($("#FieldCoordDepartOrg").val() == "other") {
                        $("#CoordDepartDepartNew").show();
                        $("#FieldCoordDepartDepartFormGroup").hide();
                        $("#FieldCoordDepartDepart").select2().load("/institutions/departments/other", function () {
                            $("#FieldCoordDepartDepart").val("other");
                        });

                    }
                }
                else {
                    $("#FieldCoordDepartDepartFormGroup").hide();
                    $("#CoordDepartDepartNew").hide();
                }
            }).trigger("change");


            //  Button Εισαγωγής Διαχειριστη
            $("#CoordDepartLink").click(function () {
                // $("#FieldCoordDepartDepartFormGroup").hide();
                // $("#CoordDepartOrgNew").hide();
                // $("#CoordDepartDepartNew").hide();
                // KeepFieldCoordDepartOrg = $("#FieldCoordDepartOrg").val();
                if ($("#FieldCoordDepartOrg").val() == "other" && $("#FieldCoordDepartRole").val() == "InstitutionAdministrator") {
                    $("#CoordDepartDepartNew").hide();
                    $("#CoordDepartOrgNew").show();
                    $("#FieldCoordDepartDepartFormGroup").hide();
                } else if ($("#FieldCoordDepartOrg").val() == "other" && $("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {
                    $("#CoordDepartDepartNew").show();
                    $("#CoordDepartOrgNew").show();
                    $("#FieldCoordDepartDepartFormGroup").hide();
                } else if ($("#FieldCoordDepartOrg").val() !== "other" && $("#FieldCoordDepartRole").val() == "DepartmentAdministrator") {
                    $("#CoordDepartDepartNew").hide();
                    $("#CoordDepartOrgNew").hide();
                    $("#FieldCoordDepartDepartFormGroup").show();
                }
                KeepFieldCoordDepartRole = $("#FieldCoordDepartRole").val();
            });

            // Close Button στο modal Διαχειριστή
            $("#CoordDepartModalButtonClose").click(function () {
                $("#CoordDepart").modal("hide");
            });

        });
    </script>
@endsection
@section('extra-css')
    <style>
        #main-slider {
            background-image: url(/images/slider-prosbasi.jpg);
        }
        .img-center {
            display: block;
            margin: 0 auto;
        }
        .font-counter {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
        }
        .counter-small {
            font-size: 18px;
            color: #fff;
            padding-top: 5px;
        }
        h2.orange {
            font-size: 28px;
            padding: 0;
            margin-bottom: 0;
        }
        .large.tooltip-inner {
            max-width: 350px;
            width: 350px;
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
@section('access-active')
    class="active"
@endsection

@section('content')

    <!--/#main-slider-->
    <section id="main-slider" class="carousel">
        <div class="carousel-inner">
            <div class="item active">
                <div class="container">
                    <div class="carousel-content">
                        <h1>&nbsp;</h1>
                        <p class="lead carousel-shadow">&nbsp;</p>
                    </div>
                </div>
            </div><!--/.item-->
        </div><!--/.carousel-inner-->
        <!--      <a class="prev" href="#main-slider" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
              <a class="next" href="#main-slider" data-slide="next"><i class="fa fa-chevron-right"></i></a> -->
    </section><!--/#main-slider-->

    <section id="Index">
        <div class="container">
            <div class="box first" style="padding: 30px 50px">
                <div class="row">
                    <div class="col-md-12">
                        @if ($errors->any())
                            @if($errors->has('email') && str_contains(head($errors->get('email')), trans('site.emailNotUnique')))
                                <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                                    <strong>
                                        {{trans('site.applicationNotSaved')}}</strong>
                                    <li>{!! head($errors->get('email')) !!}</li>
                                </ul>
                            @else
                                <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                                    <strong>
                                    <!--{{trans('site.selectInstitution')}}-->
                                        {{trans('site.click')}} <a data-toggle="modal" href="#CoordDepart"
                                                                   id="CoordDepartLink">{{trans('site.here')}}</a> {{trans('site.toEdit')}}
                                        .</strong>
                                    @foreach($errors->all() as $error)
                                        <li>{!! $error !!}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                        @if (session('message'))
                            <div class="alert alert-info" style="margin: 0px 0px 10px 0px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {{ session('message') }}
                            </div>
                        @endif
                        <h1 style="color:#52B6EC">{{trans('site.access')}}</h1>
                        <hr>
                        <p>{{trans('site.accessText1')}}</p>
                        <div class="gap"></div>
                        <h2 class="orange"> {{trans('site.userAccess')}} </h2>
                        <hr>
                        <p>{{trans('site.accessText2')}}</p>
                        <p>{{trans('site.accessText3')}}</p>
{{--                        <div class="gap"></div>--}}
{{--                        <h2 class="orange"> {{trans('site.moderatorAccess')}} </h2>--}}
{{--                        <hr>--}}
{{--                        <p>{{trans('site.moderatorText1')}} <a data-toggle="modal" href="#CoordDepart"--}}
{{--                                                               id="CoordDepartLink">{{trans('site.moderatorText2')}}</a>. {{trans('site.moderatorText3')}}--}}
{{--                        </p>--}}
                    </div>
                </div><!--/.row-->
            </div><!--/.box-->
        </div><!--/.container-->

        <!-- MODAL CoordOrg start -->
        <div class="modal fade" id="CoordDepart" tabindex="-1" data-focus-on="input:first" role="dialog"
             aria-labelledby="CoordDepartModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="CoordDepartLabel">{{trans('site.moderatorApplication')}}</h4>
                    </div> <!-- .modal-header -->
                    <div class="modal-body">

                        @if(Auth::check())
                            <div class="alert alert-danger" style="margin: 0px 0px 10px 0px">
                                {{trans('site.existingAccountError')}} <a
                                        href="/request_role_change">{{trans('site.here')}}</a>.
                            </div>
                        @else
                            <div id="selectAuthentication">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="error-template text-center" style="margin-bottom:20px;">
                                            <p>{{trans('site.clickEnterSsoToRequestRole')}}</p>
                                            <div class="row">
                                                <div class="col-md-8 col-md-offset-2 col-sm-6 col-sm-offset-3">
                                                    <a class="btn btn-primary" style="width:100%" href="/access_sso_login">
                                                        <div class="ssoRow">
                                                            <div class="ssoColumn"><span class="fa fa-graduation-cap"
                                                                                         style="font-size:20px"></span>
                                                            </div>
                                                            <div class="ssoColumn"
                                                                 style="width:100%;">{!!trans('site.loginSso')!!}</div>
                                                            <div class="ssoColumn"><span
                                                                        class="glyphicon glyphicon-chevron-right"></span>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <p>{{trans('site.LocalToRequestRole')}}</p>
                                        <div class="col-md-12 text-center">
                                            <p><a href="#"
                                                  id="localApplication">{{trans('site.Local_user_application')}}</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="applicationForm">
                                {!! Form::open(array('url' => 'store_admin_application', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'CoordDepartForm', 'role' => 'form')) !!}
                                {!! Honeypot::generate('my_name', 'my_time') !!}
                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartSurname', trans('site.lastName').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('lastname',  null, ['class' => 'form-control', 'placeholder' => trans('site.lastNameRequired'), 'id' => 'FieldCoordDepartSurname']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartΝame', trans('site.firstName').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('firstname', null, ['class' => 'form-control', 'placeholder' => trans('site.firstNameRequired'), 'id' => 'FieldCoordDepartΝame']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => trans('site.emailRequired'), 'id' => 'FieldUserEmail']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>


                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartPhone', trans('site.phone').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('telephone', null, ['class' => 'form-control', 'placeholder' => trans('site.phoneRequired'), 'id' => 'FieldCoordDepartPhone']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartRole', trans('site.role').':', ['class' => 'control-label col-sm-4']) !!}
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            {!! Form::select('role', ['' => ''] + ['InstitutionAdministrator' => trans('site.institutionModerator'), 'DepartmentAdministrator' => trans('site.departmentModerator')], null, ['id' => 'FieldCoordDepartRole', 'style' => 'width: 100%', 'aria-describedby' => 'helpBlockRole'])!!}
                                            <span id="helpBlockRole" class="help-block"
                                                  style="text-align: left;">{{trans('site.selectRole')}}</span>
                                        </div>
                                    </div>
                                </div>

                                <h4 style=" padding-top:15px; padding-bottom:5px; border-bottom: 1px solid #bcbcbc">
                                    <span class="glyphicon glyphicon-wrench"></span> {{trans('site.moderateConferencesFor')}}
                                    :</h4>


                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartOrg', trans('site.institution').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::select('institution_id', ['' => ''] + App\Institution::orderBy('title')->pluck('title', 'id')->toArray() + ['other' => trans('site.other')], null, ['id' => 'FieldCoordDepartOrg', 'style' => 'width: 100%'])!!}
                                    </div>
                                </div>

                                <div class="form-group" id="CoordDepartOrgNew">
                                    {!! Form::label('FieldCoordDepartOrgNew', trans('site.newInstitution').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('new_institution', null, ['class' => 'form-control', 'placeholder' => trans('site.enterInstitution'), 'id' => 'FieldCoordDepartOrgNew']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>

                                <div class="form-group" id="FieldCoordDepartDepartFormGroup">
                                    {!! Form::label('FieldCoordDepartDepart', trans('site.department').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::select('department_id', ['' => trans('site.selectInstitutionFirst')], null, ['id' => 'FieldCoordDepartDepart', 'style' => 'width: 100%'])!!}
                                    </div>
                                </div>

                                <div class="form-group" id="CoordDepartDepartNew">
                                    {!! Form::label('FieldCoordDepartDepartNew', trans('site.newDepartment').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::text('new_department', null, ['class' => 'form-control', 'placeholder' => trans('site.enterDepartment'), 'id' => 'FieldCoordDepartDepartNew']) !!}
                                        <div class="help-block with-errors" style="margin:0px;"></div>
                                    </div>
                                </div>


                                <div class="form-group" id="FieldCoordDepartDepartFormGroup">
                                    {!! Form::label('FieldCoordDepartComment', trans('site.description').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-8">
                                        {!! Form::textarea('comment', null, ['class' => 'form-control', 'placeholder' => trans('site.justification'), 'id' => 'FieldCoordDepartComment', 'rows' => '3'])!!}
                                    </div>
                                </div>

                                <div class="form-group">
                                    {!! Form::label('FieldCoordDepartTerms', trans('site.termsAcceptance').':', ['class' => 'control-label col-sm-4 ']) !!}
                                    <div class="col-sm-1">
                                        {!! Form::checkbox('accept_terms', 0, false, ['id' => 'FieldCoordDepartTerms', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
                                    </div>
                                    <a data-toggle="modal" href="#termsConditions"
                                       class="col-sm-7">{{trans('site.termsTitle')}}</a>
                                </div>

                                <div class="modal-footer" style="margin-top:0px;">
                                    {!! Form::submit(trans('site.save'), ['class' => 'btn btn-primary', 'id' => 'CoordDepartSubmitBtnNew', 'name' => 'CoordDepartSubmitBtnNew']) !!}
                                    <button type="button" id="CoordDepartModalButtonClose"
                                            class="btn btn-default">{{trans('site.cancel')}}</button>
                                </div> <!-- .modal-footer -->

                                {!! Form::close() !!}
                            </div>
                        @endif
                    </div> <!-- .modal-body -->
                </div> <!-- .modal-content -->
            </div> <!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!-- modal Admin end -->

        <!-- MODAL CoordOrg start -->
        <div class="modal fade" id="termsConditions" tabindex="-1" data-focus-on="input:first" role="dialog"
             aria-labelledby="CoordDepartModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="termsConditionsLabel">{{trans('terms.ModeratorsTermsTextTitle')}}</h4>
                    </div> <!-- .modal-header -->
                    <div class="modal-body">
                        {!!trans('terms.ModeratorsTermsText')!!}
                        <div class="modal-footer" style="margin-top:0px;">
                            <button type="button" data-dismiss="modal" class="btn btn-default">OK</button>
                        </div> <!-- .modal-footer -->
                    </div> <!-- .modal-body -->
                </div> <!-- .modal-content -->
            </div> <!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!-- modal Admin end -->

    </section>
@endsection
