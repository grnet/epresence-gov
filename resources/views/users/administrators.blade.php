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

    <!-- bootstrap date-picker    -->
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/font-awesome.css">

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">

    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">


    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script src="/js/scripts.js"></script>
    <script type="text/javascript">

        function changeStatetoSso(user_id) {
            var r = confirm("Ειστε σίγουρος ότι θέλετε να μετατρέψετε αυτον τον χρήστη σε SSO ;");

            if (r === true) {
                $.post("/users/change_state_to_sso",
                    {
                        _token: '{{csrf_token()}}',
                        user_id: user_id,
                    })
                    .done(function (data) {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.replace("");
                        }
                        else
                            console.log(data);
                    });
            }
        }
        function changeStatetoLocal(user_id) {

            var r = confirm("Ειστε σίγουρος ότι θέλετε να μετατρέψετε αυτον τον χρήστη σε Local ;");
            if (r === true) {
                $.post("/users/change_state_to_local",
                    {
                        _token: '{{csrf_token()}}',
                        user_id: user_id,
                    })
                    .done(function (data) {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.replace("");
                        }
                        else
                            console.log(data);
                    });
            }
        }
        function sendConfirmationEmail(user_id) {
            var r = confirm("Ειστε σίγουρος ότι θέλετε να στείλετε email ενεργοποίησης σε αυτον τον χρήστη ;");
            if (r === true) {
                $.post("/users/resend_activation_email",
                    {
                        _token: '{{csrf_token()}}',
                        user_id: user_id,
                    })
                    .done(function (data) {
                        if (data.status === 'success') {
                            alert(data.message);
                            window.location.replace("");
                        }
                        else
                            console.log(data);
                    });
            }
        }


        $(document).ready(function () {

            var _token = '{{csrf_token()}}';

            var new_mail_field_inst = $("#FieldInstitutionAdminEmail");
            var local_state_checkbox_inst = $("#FieldInstitutionAdminLocalState");
            var sso_state_checkbox_inst = $("#FieldInstitutionAdminSsoState");
            var matched_error_container_inst = $("#matched_error_container_inst");
            var not_matched_error_container_inst = $("#not_matched_error_container_inst");

            //Check if the domain of the emails typed is in the list of the organizations domains

            new_mail_field_inst.on("keyup", function () {
                check_mail_properties(
                    new_mail_field_inst.val(),
                    matched_error_container_inst,
                    not_matched_error_container_inst,
                    local_state_checkbox_inst,
                    sso_state_checkbox_inst,
                    _token
                );
            });

            $(".user_state_radio_button_inst").on("change",function(){
                check_mail_properties(
                    new_mail_field_inst.val(),
                    matched_error_container_inst,
                    not_matched_error_container_inst,
                    local_state_checkbox_inst,
                    sso_state_checkbox_inst,
                    _token
                );
            });

            var new_mail_field_dept = $("#FieldDepartmentAdminEmail");
            var local_state_checkbox_dept = $("#FieldDepartmentAdminLocalState");
            var sso_state_checkbox_dept = $("#FieldDepartmentAdminSsoState");
            var matched_error_container_dept = $("#matched_error_container_dept");
            var not_matched_error_container_dept = $("#not_matched_error_container_dept");

            //Check if the domain of the emails typed is in the list of the organizations domains

            new_mail_field_dept.on("keyup", function () {
                check_mail_properties(
                    new_mail_field_dept.val(),
                    matched_error_container_dept,
                    not_matched_error_container_dept,
                    local_state_checkbox_dept,
                    sso_state_checkbox_dept,
                    _token
                );
            });

            $(".user_state_radio_button_dept").on("change",function(){
                check_mail_properties(
                    new_mail_field_dept.val(),
                    matched_error_container_dept,
                    not_matched_error_container_dept,
                    local_state_checkbox_dept,
                    sso_state_checkbox_dept,
                    _token
                );
            });


            // ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP
            $('[data-toggle="tooltip"]').tooltip();

            //ENABLE SUMMERNOTE -- TEXTAREA
            $('.summernote').summernote({
                lang: 'el-GR',
                height: 100,
                placeholder: '{!!trans('users.enterEmailBody')!!}'
            });

            // ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ DATATABLES

            $(".pagination").addClass("pull-right");

            $("[id^=openUserDeatils]").on("click", function () {
                var user = $(this).attr('id').split('-').pop(-1);
                if ($("#userDeatils-" + user).hasClass("out")) {
                    $("#userDeatils-" + user).addClass("in");
                    $("#userDeatils-" + user).removeClass("out");
                } else if ($("#userDeatils-" + user).hasClass("in")) {
                    $("#userDeatils-" + user).addClass("out");
                    $("#userDeatils-" + user).removeClass("in");
                } else {
                    $("#userDeatils-" + user).addClass("in");
                }
            });

            $("[id^=RowBtnDelete]").on("click", function () {
                var row = $(this).closest('tr');
                var nRow = row[0];
                var user = $(this).attr('id').split('-').pop(-1);
                $.post("/users/delete_user", {user_id: user, sure: ""})
                    .done(function (data) {
                        obj = JSON.parse(data);
                        if (obj.status == 'error') {
                            alert("" + obj.data);
                        }
                        else if (obj.status == 'are_you_sure') {
                            var r = confirm("" + obj.data);
                            if (r == true) {
                                $.post("/users/delete_user", {user_id: user, sure: "yes"})
                                    .done(function (data2) {
                                        obj2 = JSON.parse(data2);
                                        if (obj2.action == 'deleteUser') {
                                            nRow.remove();
                                            return false;
                                        } else if (obj2.action == 'disableUser') {
                                            $("#RowBtnDelete-" + user).removeClass("btn-danger").addClass("btn-success");
                                            $("#RowBtnDelete-" + user).attr('data-original-title', '{!!trans('users.userActivation')!!}');
                                            $("#SpanBtnDelete-" + user).removeClass("glyphicon-ban-circle").addClass("glyphicon-ok");
                                            $("#cellStatus-" + user).html('<span class="glyphicon glyphicon-ban-circle" aria-hidden="true"><span style="display:none">1</span></span> {{trans('users.inactive')}}');
                                        } else if (obj2.action == 'enableUser') {
                                            $("#RowBtnDelete-" + user).removeClass("btn-success").addClass("btn-danger");
                                            $("#RowBtnDelete-" + user).attr('data-original-title', '{!!trans('users.userDeactivation')!!}');
                                            $("#SpanBtnDelete-" + user).removeClass("glyphicon-ok").addClass("glyphicon-ban-circle");
                                            $("#cellStatus-" + user).html('<span class="glyphicon glyphicon-ok" aria-hidden="true"><span style="display:none">1</span></span> {{trans('users.active')}}');
                                        }
                                    });
                            }
                        }
                    });
            });


            // Advanced search
            $("#searchRole, #searchInstitution, #searchDepartment, #searchState, #searchStatus, #confirmedFilter, #acceptedTermsFilter").select2({
                containerCssClass: "select2-container-sm",
                dropdownCssClass: "tpx-select2-drop",
                allowClear: true
            });

            $("#searchRole").select2({allowClear: true, placeholder: "{!!trans('users.selectRole')!!}"});

            @if(empty(Input::get('institution')))
            $("#searchDepartment").select2({
                placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
                allowClear: true
            });
            @else
            $("#searchDepartment").select2({allowClear: true, placeholder: "{!!trans('users.selectDepartment')!!}"});
            @endif

            $("#searchState").select2({allowClear: true, placeholder: "{!!trans('users.localUser')!!}"});
            $("#searchStatus").select2({allowClear: true, placeholder: "{!!trans('users.selectStatus')!!}"});
            $("#searchMultiMails").select2({allowClear: true, placeholder: "{!!trans('users.searchMultiMails')!!}"});
            $("#confirmedFilter").select2({
                allowClear: true,
                placeholder: "{!!trans('users.confirmedFilterPlaceholder')!!}"
            });
            $("#acceptedTermsFilter").select2({allowClear: true, placeholder: "{!!trans('site.termsAcceptance')!!}"});

            $("#searchInstitution").select2({
                allowClear: true, placeholder: "{!!trans('users.selectInstitution')!!}"
            }).on("change", function () {

                if ($("#searchInstitution").val() > 0) {
                    $("#searchDepartment").select2("val", "");
                    $("#searchDepartment").select2({
                        allowClear: true,
                        placeholder: "{!!trans('users.selectDepartment')!!}"
                    }).load("/institutions/departments_with_other/" + $("#searchInstitution").val());
                } else if ($("#searchInstitution").val() == "") {
                    $("#searchDepartment").select2("data", null, {
                        placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
                        allowClear: true
                    });
                }
            });

            $('.datepicker').datepicker({
                format: "dd-mm-yyyy",
                todayBtn: "linked",
                language: "el",
                autoclose: true,
                todayHighlight: true
            });

            // Get url parameters
            $.urlParam = function (name) {
                var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
                return results[1] || 0;
            };

            $("#datatablesChangeDisplayLength").val({{ isset($_GET['limit']) ? $_GET['limit'] : 10 }});

            // Table limits
            $("#datatablesChangeDisplayLength").change(function () {
                var value = $("select option:selected").val();
                var limit = value;
                if (value === "-1") {
                    var limit = <?php if (!empty($users)) {
                        echo $users->total();
                    } else {
                        echo 0;
                    } ?>;
                }

                var url = window.location.href;
                var url_pathname = window.location.pathname;
                var current_param = window.location.search.substring(1);
                if (current_param != null) {
                    if (url.search("page") > 0) {
                        current_param = current_param.replace("page=" + $.urlParam("page"), "");
                        current_param = current_param.replace("&page=" + $.urlParam("page"), "");
                    }
                    current_param = current_param + "&";
                }

                if (url.search("limit") < 0) {
                    var params = [{name: "limit", value: limit}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                } else if (url.search("limit") > 0) {
                    current_param = current_param.replace("&limit=" + $.urlParam("limit"), "");
                    current_param = current_param.replace("limit=" + $.urlParam("limit"), "");
                    var params = [{name: "limit", value: limit}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }
            });

            // Sort table
            //Default class

            var sortings = ["createdAt", "lastname", "state", "status"];
            var url = window.location.href;

            $.each(sortings, function (index, value) {

                var sort_selection = $("#sort_" + value);

                if (url.search("sort_" + value) > 0 && value !== "lastname") {

                    var sort_created_at =   $("#sort_createdAt");

                    sort_selection.removeClass("sorting");
                    sort_selection.addClass("sorting" + $.urlParam("sort_" + value));
                    sort_created_at.removeClass("sortingasc");
                    sort_created_at.addClass("sorting");
                } else if (url.search("sort_" + value) > 0 && value === "lastname") {
                    sort_selection.removeClass("sortingdesc");
                    sort_selection.addClass("sorting" + $.urlParam("sort_" + value));
                }
            });

            $("[id^=sort]").on("click", function () {
                var params = null;
                var sortings = ["createdAt", "lastname", "state", "status"];
                var col = $(this).attr('id').split('_').pop(-1);
                var url = window.location.href;
                var url_pathname = window.location.pathname;
                // alert(url.search("sort_" + col));
                var current_param = window.location.search.substring(1);
                if (current_param != null) {
                    current_param = current_param + "&";
                }

                if (url.search("sort_" + col) < 0) {
                    $.each(sortings, function (index, value) {
                        current_param = current_param.replace("&sort_" + value + "=asc", "");
                        current_param = current_param.replace("&sort_" + value + "=desc", "");
                    });
                    params = [{name: "sort_" + col, value: "asc"}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }

                else if (url.search("sort_" + col) > 0) {

                    var variable = $.urlParam("sort_" + col);

                    var new_var = "desc";

                    if (variable === "desc") {
                       new_var = "asc";
                    }

                    $.each(sortings, function (index, value) {
                        current_param = current_param.replace("&sort_" + value + "=asc", "");
                        current_param = current_param.replace("&sort_" + value + "=desc", "");
                    });
                    params = [{name: "sort_" + col, value: new_var}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }
            });


            $("#SendCoordinatorsEmail").click(function () {
                $("#CoordMail").modal("show");
            });

            $("#SendCoordinatorsEmailButtonClose").click(function () {
                $("#CoordMail").modal("hide");
            });

            $("#SendCoordinatorsEmail").click(function () {
                $("#CoordMail").modal("show");
            });

            $("#SendCoordinatorsEmailButtonClose").click(function () {
                $("#CoordMail").modal("hide");
            });

          });

         </script>

          @if(Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator'))
                 @include('users.addDepartmentAdminScripts')
             @if(Auth::user()->hasRole('SuperAdmin'))
                 @include('users.addInstitutionAdminScripts')
             @endif
         @endif
@endsection
@section('extra-css')
    <style>

        #matched_error_container_dept{
            display:none;
        }

        #not_matched_error_container_dept{
            display:none;
        }

        #matched_error_container_inst{
            display:none;
        }

        #not_matched_error_container_inst{
            display:none;
        }


        .container {
            min-width: 400px !important;
            text-align: left;
        }

        .zero-width {
            display: none;
            width: 0px;
        }

        table#userTable th {
            font-size: 12px;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
        }

        table#userTable td {
            padding-left: 5px !important;
            padding-right: 5px !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }

        .cellDetails {
            min-width: 30px !important;
        }

        .cellName {
            width: 240px !important;
        }

        .cellRole {
            width: 160px !important;
        }

        .cellState {
            width: 100px !important;
        }

        .cellOrg {
            width: 170px !important;
        }

        .cellDepart {
            width: 120px !important;
        }

        /*
        .cellType {
            width: 80px !important;
        } */
        .cellStatus {
            width: 100px !important;
        }

        .cellCreationDate {
            width: 100px !important;
        }

        .cellButton {
            padding: 3px !important;
            width: 90px !important;
            min-width: 90px !important;
            max-width: 90px !important;
        }

        tfoot {
            display: table-header-group;
        }

        table thead .sorting:before {
            font-family: 'Glyphicons Halflings';
            content: "\e150";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table thead .sortingasc:before {
            font-family: 'Glyphicons Halflings';
            content: "\e155";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table thead .sortingdesc:before {
            font-family: 'Glyphicons Halflings';
            content: "\e156";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table thead .sorting, table thead .sortingasc, table thead .sortingdesc {
            cursor: pointer;
        }

        .user_details {
            cursor: pointer;
        }

        .hiddenRow {
            padding: 0 !important;
        }

        .newdep {
            margin-top: 5px !important;
        }
    </style>
@endsection
@section('users-active')
    class = "active"
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <!-- Tab line -START -->
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            @can('view_admins_menu')
                                <li class="active"><a href="#">{{trans('users.moderators')}}</a></li>
                            @endcan
                            @can('view_users')
                                <li><a href="/users">{{trans('users.users')}}</a></li>
                            @endcan
                            @can('view_applications')
                                <li><a href="/administrators/applications">{{trans('users.waitingApproval')}}</a></li>
                            @endcan
                        </ul>
                    </div>
                </div>
                <!-- Tab line -END -->

                <div class="small-gap"></div>

                @if (session('storesSuccessfully'))
                    <div class="alert alert-info" role="alert" style="margin: 0px 15px 10px 15px">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ session('storesSuccessfully') }}
                    </div>
                @elseif (session('message'))
                    <div class="alert alert-info">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        {{ session('message') }}
                    </div>
                @elseif ($errors->new_dep_admin->any())
                    <div class="alert alert-danger" id="errorsDiv">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>{{trans('users.userNotSaved1')}} <a data-toggle="modal" href="#DepartmentAdminModal">{{trans('users.here')}}</a> {{trans('users.userNotSaved2')}}
                            .</strong>
                        <ul>
                            @foreach($errors->new_dep_admin->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @elseif($errors->new_inst_admin->any())
                    <div class="alert alert-danger" id="errorsDiv">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>{{trans('users.userNotSaved1')}} <a data-toggle="modal" href="#InstitutionAdminModal">{{trans('users.here')}}</a> {{trans('users.userNotSaved2')}}
                            .</strong>
                        <ul>
                            @foreach($errors->new_inst_admin->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    </div>
                @elseif (session('email_errors'))
                    <ul class="alert alert-danger" style="margin: 0 15px 10px 15px">
                        <strong>{{trans('users.emailNotSent')}}</strong>
                        @foreach(session('email_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

            <!-- DATATABLES START -->
                <div class="row"> <!-- Row with search field and add button - START -->
                    <div class="col-md-5 col-sm-12 col-xs-12">
						<span class="pull-left" style="width:110px">
							<div class="input-group">
								<span class="input-group-addon"><i class="glyphicon glyphicon-align-justify"></i></span>
								<select class="form-control" id="datatablesChangeDisplayLength">
									<option value="10">10</option>
									<option value="20">20</option>
									<option value="30">30</option>
									<option value="50">50</option>
									<option value="100">100</option>
									<option value="{{ $users->total() }}">All</option>
								</select>
							</div>
						</span>
                        <span class="pull-left">
                            <div class="input-group" style="width:200px">
								<a class="btn btn-primary" role="button" data-toggle="collapse"
                                   href="#collapseAdvancedSearch" aria-expanded="false"
                                   aria-controls="collapseAdvancedSearch" style="margin-left:5px;">{{trans('users.search')}}
                                    <span class="glyphicon glyphicon-search" aria-hidden="true"></span></a>
                            </div>
                        </span>
                    </div>
                    <div class="col-md-7 col-sm-12 col-xs-12" style="text-align:right">
                        <div>
                            @if((Auth::user()->hasRole('SuperAdmin'))|| Auth::user()->hasRole('InstitutionAdministrator'))
                                @if(Auth::user()->hasRole('SuperAdmin'))
                                    <button type="button" class="btn btn-success" data-toggle="modal"
                                            style="padding-right:6px; padding-left:6px" id="NewInstitutionAdminButton">
                                        <small><span
                                                    class="glyphicon glyphicon-plus-sign"></span> {{trans('users.addInstitutionModerator')}}
                                        </small>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-success" data-toggle="modal"
                                        style="padding-right:6px; padding-left:6px" id="NewDepartmentAdminButton">
                                    <small><span
                                                class="glyphicon glyphicon-plus-sign"></span> {{trans('users.addDepartmentModerator')}}
                                    </small>
                                </button>
                                <button type="button" class="btn btn-primary"
                                        style="padding-right:6px; padding-left:6px" data-toggle="modal"
                                        data-original-title="{{trans('users.emailAllModerators')}}"
                                        id="SendCoordinatorsEmail" data-toggle="tooltip" data-placement="top" title="">
                                    <span class="glyphicon glyphicon-envelope"></span> {{trans('users.sendEmail')}}
                                </button>

                            @endif
                        </div>
                    </div>
                </div> <!-- Row with search field and add button - END -->
                @include('users._advancedSearch', [])
                @include('users._userTable', [])
            </div><!--/.box-->
        </div><!--/.container-->

    @if(Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('InstitutionAdministrator'))
        @if(Auth::user()->hasRole('SuperAdmin'))
          @include('users.addInstitutionAdminModal')
        @endif
       @include('users.addDepartmentAdminModal')
    @endif

    <!-- modal Email Coordinators start -->
        <div class="modal fade" id="CoordMail" tabindex="-1" role="dialog" aria-labelledby="CoordOrgModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title">{{trans('users.emailAllModerators')}}</h4>
                    </div> <!-- .modal-header -->
                    <div class="modal-body">


                        {!! Form::open(array('url' => 'administrators/sendEmailToCoordinators', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'EmailCoordinatorForm', 'role' => 'form')) !!}
                        <div class="form-group">
                            {!! Form::label('FieldTitle', trans('users.subject').':', ['class' => 'control-label col-sm-2 ']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('title', null, ['class' => 'form-control', 'id' => 'FieldTitle']) !!}
                                <div class="help-block with-errors" style="margin:0px;"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="FieldInvMessage" class="control-label col-sm-2"
                                   style="margin-bottom:5px">{{trans('users.messageBody')}}:</label>
                            <div class="col-sm-10">
                                <!-- <div id="FieldInvMessage" class="summernote"></div> -->
                                {!! Form::textarea('text', null, ['class' => 'summernote', 'id' => 'FieldInvMessage']) !!}
                            </div>
                        </div>
                        <div class="modal-footer" style="margin-top:0px;">

                            {!! Form::submit(trans('users.send'), ['class' => 'btn btn-primary', 'id' => 'EmailCoordinatorSubmit', 'name' => 'EmailCoordinatorSubmit']) !!}
                            <button type="button" id="SendCoordinatorsEmailButtonClose"
                                    class="btn btn-default">{{trans('users.cancel')}}</button>
                        </div> <!-- .modal-footer -->
                        {!! Form::close() !!}
                    </div> <!-- .modal-body -->
                </div> <!-- .modal-content -->
            </div> <!-- .modal-dialog -->
        </div>
        <!-- modal Email Coordinators end -->

    </section>

@endsection
