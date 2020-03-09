@extends('app')

@section('header-javascript')
    <link href="select2/select2.css" rel="stylesheet">
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

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">
    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="datatables/date-eu.js"></script>
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
           var new_mail_field = $("#FieldUserEmail");
           var local_state_checkbox = $("#FieldLocalState");
           var sso_state_checkbox = $("#FieldSsoState");
           var matched_error_container = $("#matched_error_container");
           var not_matched_error_container = $("#not_matched_error_container");

            //Check if the domain of the emails typed is in the list of the organizations domains

           new_mail_field.on("keyup", function () {
               check_mail_properties(
                   new_mail_field.val(),
                   matched_error_container,
                   not_matched_error_container,
                   local_state_checkbox,
                   sso_state_checkbox,
                   _token
               );
           });

            $(".user_state_radio_button").on("change",function(){
                check_mail_properties(
                    new_mail_field.val(),
                    matched_error_container,
                    not_matched_error_container,
                    local_state_checkbox,
                    sso_state_checkbox,
                    _token);
            });


        $('[data-toggle="popover"]').popover();

        $("#UserOrgNew").hide();
        $("#UserDepartNew").hide();
        $("#FieldUserDepart").select2({placeholder: "{!!trans('users.selectInstitutionFirst')!!}", allowClear: true});

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
                    placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
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

        //Load departments for VidyoRoom
        $("#FieldVRoomOrg").change(function () {
            $("#FieldVRoomDepart").load("/institutions/departments/" + $("#FieldVRoomOrg").val());
        });

        $('.datepicker').datepicker({
            format: "dd-mm-yyyy",
            todayBtn: "linked",
            language: "el",
            autoclose: true,
            todayHighlight: true
        });

        // ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP
        $('[data-toggle="tooltip"]').tooltip();

        $("[id^=openUserDeatils]").on("click", function () {
            var user = $(this).attr('id').split('-').pop(-1);

            var user_details = $("#userDeatils-" + user);

            if (user_details.hasClass("out")) {
                user_details.addClass("in");
                user_details.removeClass("out");
            } else if ($("#userDeatils-" + user).hasClass("in")) {
                user_details.addClass("out");
                user_details.removeClass("in");
            } else {
                user_details.addClass("in");
            }
        });

        $(".pagination").addClass("pull-right");

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
        $("#searchDepartment").select2({placeholder: "{!!trans('users.selectInstitutionFirst')!!}", allowClear: true});
        @else
        $("#searchDepartment").select2({allowClear: true,placeholder: "{!!trans('users.selectDepartment')!!}"});
        @endif


        $("#searchState").select2({allowClear: true, placeholder: "{!!trans('users.localUser')!!}"});
        $("#searchStatus").select2({allowClear: true, placeholder: "{!!trans('users.selectStatus')!!}"});
        $("#searchMultiMails").select2({allowClear: true, placeholder: "{!!trans('users.searchMultiMails')!!}"});
        $("#confirmedFilter").select2({allowClear: true, placeholder: "{!!trans('users.confirmedFilterPlaceholder')!!}"});
        $("#acceptedTermsFilter").select2({allowClear: true, placeholder: "{!!trans('site.termsAcceptance')!!}"});


        $("#searchInstitution").select2({
            allowClear: true, placeholder: "{!!trans('users.selectInstitution')!!}"
        }).on("change", function () {

            if ($("#searchInstitution").val() > 0) {
                $("#searchDepartment").select2("val", "");
                $("#searchDepartment").select2({allowClear: true,placeholder: "{!!trans('users.selectDepartment')!!}"}).load("/institutions/departments_with_other/" + $("#searchInstitution").val());
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
        }

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
                    current_param = current_param.replace("&page=" + $.urlParam("page"), "");
                    current_param = current_param.replace("page=" + $.urlParam("page"), "");
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
            if (url.search("sort_" + value) > 0 && value != "lastname") {
                $("#sort_" + value).removeClass("sorting");
                $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
                $("#sort_createdAt").removeClass("sortingasc");
                $("#sort_createdAt").addClass("sorting");
            } else if (url.search("sort_" + value) > 0 && value == "lastname") {
                $("#sort_" + value).removeClass("sortingdesc");
                $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
            }
        });

        $("[id^=sort]").on("click", function () {
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
                var params = [{name: "sort_" + col, value: "asc"}];
                window.location.assign(url_pathname + "?" + current_param + $.param(params));
            }

            else if (url.search("sort_" + col) > 0) {
                var variable = $.urlParam("sort_" + col);
                var new_var = "desc";
                if (variable === "desc") {
                    var new_var = "asc";
                }
                $.each(sortings, function (index, value) {
                    current_param = current_param.replace("&sort_" + value + "=asc", "");
                    current_param = current_param.replace("&sort_" + value + "=desc", "");
                });
                var params = [{name: "sort_" + col, value: new_var}];
                window.location.assign(url_pathname + "?" + current_param + $.param(params));
            }
        });

        // ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΟ ΦΥΣΙΚΟ ΠΡΟΣΩΠΟ

        // για ελεγχο αποστολής μηνύματος ενεργοποιησης/απενεργγοποιησης ΦΠ
        var KeepFieldUserStatus;
        $('#FieldUserStatus').on("change", function (evt) {
            CurrentFieldStatus = $('#FieldUserStatus').val();
            if (CurrentFieldStatus == 1) {
                $("#FieldUserStatusAlert").hide();
            }
            else if (CurrentFieldStatus == 0) {
                $("#FieldUserStatusAlert").show();
                $("#SendUserEmail").val(1);
                $("#FieldUserStatusMessage").text("{!!trans('users.activationSelected')!!}: ");
            }
        });


        var KeepFieldUserPassword;
        $('#FieldUserPassword').on("keyup change", function (evt) {
            CurrentFieldStatus = $('#FieldUserPassword').val();
            if (CurrentFieldStatus == KeepFieldUserPassword) {
                $("#FieldUserPasswordAlert").hide();
            } else {
                $("#FieldUserPasswordAlert").show();
            }
        });


        //  Button Εισαγωγής ΦΠ
        $("#NewUser").click(function () {
            $('#FieldUserStatus').val(1);
            $('#FieldUserStatus').checkboxX('refresh');
            $("#FieldUserStatusAlert").show();
            $("#FieldUserStatusMessage").text("{!!trans('users.activationSelected')!!}: ");
            $("#FieldUserPasswordAlert").hide();
            KeepFieldUserStatus = $('#FieldUserStatus').val();
            KeepFieldUserPassword = $('#FieldUserPassword').val();
            $("#UserModal").modal("show");
        });

        $("#Form").submit(function (event) {
            $("#UserModal").modal("show");
        });


        // Close Button στο modal ΦΠ
        $("#UserModalButtonClose").click(function () {
            // Close Button στο modal Διαχειριστή
            $("[id^=FieldUser]").val("");
            $('input:radio').prop("checked", false);
            $("#FieldUserOrg").select2("data", null);
            $("#FieldUserDepart").select2("data", null);
            $("#UserDepart").show();
            $("#UserDepartNew").hide();
            $("#UserOrgNew").hide();
            $("#errorsDiv").hide();
            $("#UserModal").modal("hide");
        });
        })
        ;
    </script>

@endsection
@section('extra-css')
    <style>

        #matched_error_container{
            display:none;
        }

        #not_matched_error_container{
            display:none;
        }

        .container {
            min-width: 400px !important;
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
                                <li><a href="/administrators">{{trans('users.moderators')}}</a></li>
                            @endcan
                            @can('view_users')
                                <li class="active"><a href="/users">{{trans('users.users')}}</a></li>
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

                @elseif ($errors->any())
                    <div class="alert alert-danger" id="errorsDiv">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <strong>{{trans('users.userNotSaved1')}} <a data-toggle="modal" href="#UserModal"
                                                                    id="NewUserEdit">{{trans('users.here')}}</a> {{trans('users.userNotSaved2')}}
                            .</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
            @endif

            <!-- DATATABLES START -->

                <div class="row"> <!-- Row with search field and add button - START -->
                    <div class="col-md-5 col-sm-12 col-xs-12">
                        <span class="pull-left" style="width:110px">
                               <div class="input-group">
                                    <span class="input-group-addon"><i
                                                class="glyphicon glyphicon-align-justify"></i></span>
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
                            <button type="button" class="btn btn-success" style="padding-right:6px; padding-left:6px"
                                    data-toggle="modal" id="NewUser">
                                <small><span class="glyphicon glyphicon-plus-sign"></span> {{trans('users.addUser')}}
                                </small>
                            </button>
                        </div>
                    </div>
                </div> <!-- Row with search field and add button - END -->

            @include('users._advancedSearch', [])

            @include('users._userTable', [])

            <!-- DATATABLES END -->

            </div><!--/.box-->
        </div><!--/.container-->

        @include('users.addUserModal')
    </section>
@endsection
