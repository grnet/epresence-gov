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
                            <div class="alert alert-info" style="margin: 0 0 10px 0">
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
                        <div class="gap"></div>
                        <h2 class="orange"> {{trans('site.moderatorAccess')}} </h2>
                        <hr>
                        <p>{!!trans('site.moderatorText1')!!}</p>
                    </div>
                </div><!--/.row-->
            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection
