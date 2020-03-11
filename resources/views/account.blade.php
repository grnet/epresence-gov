@extends('app')

@section('header-javascript')

    <link href="select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="select2/select2.js"></script>
    <script type="text/javascript" src="select2/select2_locale_el.js"></script>


    <!-- checkbox -->
    <script src="bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="bootstrap-checkbox-x/checkbox-x.css">

    <link rel="stylesheet" href="css/font-awesome.css">
    <link href="css/main.css" rel="stylesheet">


    <!-- DATATABLES ΓΙΑ ΙΣΤΟΡΙΚΟ -->
    <script type="text/javascript" src="datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="datatables/date-eu.js"></script>
    <link href="datatables/dataTables.bootstrap.css" rel="stylesheet">



    <script type="text/javascript">
        $(document).ready(function () {

            var new_org_container = $("#NewOrgContainer");
            var new_dep_container = $("#NewDepContainer");
            var dep_container = $("#DepContainer");
            var department_select_field = $("#FieldUserDepart");
            var new_custom_department_field = $("#FieldUserDepartNew");
            var organization_select_field = $("#FieldUserOrg");
            // var new_custom_department_field = $("#FieldUserOrgNew");

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();

            // Delete user image
            $("#deleteMyUserImage").click(function (event) {
                event.preventDefault();
                $.post("/users/delete_user_image", {id: {{$user->id}} })
                    .done(function (data) {
                        obj = JSON.parse(data);
                        if (obj.status === 'success') {
                            $(".card").hide();
                            $(".userImage").html('<span class="glyphicon glyphicon-user icon-size"></span>');
                        }
                    });
            });
            department_select_field.select2({placeholder: "{!!trans('site.selectInstitutionFirst')!!}"});

            // UserDepart On change
            department_select_field.on("change", function () {

                if (department_select_field.val() === "other") {
                    new_dep_container.show();
                    new_custom_department_field.show();
                } else if (department_select_field.val() !== "other" && department_select_field.val() > 0) {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
                else if (department_select_field.val() === "") {
                    new_dep_container.hide();
                    new_custom_department_field.hide();
                }
            }).trigger("change");

            //Application methods


            $( "#localApplication" ).click(function() {
                $("#selectAuthentication").slideUp();
                $("#applicationForm").show();
            });

            // ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΟΝ ΣΥΝΤΟΝΙΣΤΗ ΤΜΗΜΑΤΟΣ

            $("#FieldCoordDepartRole").select2({placeholder: "{!!trans('site.selectRoleRequired')!!}"});
            $( "#FieldCoordDepartTerms" ).on("change", function() {
                if ($("#FieldCoordDepartTerms").val()==0) {
                    $( "#CoordDepartSubmitBtnNew" ).addClass("disabled");
                }else {
                    $( "#CoordDepartSubmitBtnNew" ).removeClass("disabled");
                }
            });
            // Close Button στο modal Διαχειριστή
            $("#CoordDepartModalButtonClose").click(function() {
                $("#CoordDepart").modal("hide");
            });



            //  Ορισμός Πίνακα DataTable


            $("#example").dataTable({
                "oLanguage": {
                    "sZeroRecords": "{{trans('site.noConferencesFound')}}",
                    "sInfoFiltered": "({{trans('site.fromNoConferences')}})",
                },
                "aoColumns": [
                    {"sClass": "cellDesc"},
                    {"sClass": "cellStartDate", "sType": "date-eu"},
                    {"sClass": "cellStartTime"},
                    {"sClass": "cellEndTime"},
                    {"sClass": "cellEndTime"},
                ]
            });
            var oTable = $("#example").dataTable();

            function changeDisplayLength(oTable, iDisplayLength) {
                var oSettings = oTable.fnSettings();
                oSettings._iDisplayLength = iDisplayLength;
                oTable.fnDraw();
            }

            $("#datatablesChangeDisplayLength").change(function () {
                changeDisplayLength(oTable, +($(this).val()));
            });

            $("#datatablesChangeDisplayConnected").change(function () {

                let val = $(this).val();
                if(val !== 'all'){
                    oTable.fnFilter($(this).val(),4);
                }else{
                    oTable.fnFilter("",4);
                }
            });

            $("#datatablesSearchTextField").keyup(function () {
                oTable.fnFilter($(this).val());
            });


            //  BUTTONS ΕΜΦΑΝΙΣΗΣ ΙΣΤΟΡΙΚΟΥ & ΣΤΟΙΧΕΙΩΝ ΧΡΗΣΤΗ
            $("#ShowHistory").click(function () {
                $("#HistModal").modal("show");
            });

            $("#ShowDetails").click(function () {
                $("#UserMessage").hide();
                $("#FieldUserPasswordAlert").hide();
                KeepFieldUserPassword = $('#FieldUserPassword').val();
                $("#UserModal").modal("show");
            });

            //  ΣΥΝΑΡΤΗΣΕΙΣ ΓΙΑ ΤΗΝ ΕΜΦΑΝΙΣΗ / ΔΙΟΡΘΩΣΗ ΣΤΟΙΧΕΙΩΝ ΧΡΗΣΤΗ
            // Close Button στο modal ΦΠ
            $("#UserModalButtonClose").click(function () {
                $("#UserModal").modal("hide");
            });

            @if($canBeDeleted)
            $("#DeleteAccountButton").click(function () {
                $("#DeleteAccountModal").modal("show");
            });
            @endif

            $("#RoleChangeRequest").click(function () {
                $("#RequestRoleChangeModal").modal("show");
            });
            @if($errors->any())
            @if($errors->has('delete_account_confirmation_email') || $errors->has('confirmation_email_not_matched'))
            $("#DeleteAccountModal").modal("show");
            @elseif($errors->has('application_comment') || $errors->has('accept_terms') || $errors->has('application_role') || $errors->has('application_telephone'))
            $("#RequestRoleChangeModal").modal("show");
            @else
            $("#UserModal").modal("show");
            @endif
            @endif
            @if($canRequestRoleChange)

            let RoleChangeDepartmentSelect = $("#FieldRoleChangeDepartmentId");

            RoleChangeDepartmentSelect.select2({
                allowClear: true,
                placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
            });

            @if(!$user->hasRole('DepartmentAdministrator'))
            let RoleChangeInstitutionSelect = $("#FieldRoleChangeInstitutionId");

            RoleChangeInstitutionSelect.select2({
                allowClear: true,
                placeholder: "{!!trans('users.selectInstitutionRequired')!!}"
            }).on("change", function () {
                update_ia_inst_selection_ui(true);
            });
            @endif

            update_ia_inst_selection_ui(false);
            function update_ia_inst_selection_ui(load_departments){
                if(RoleChangeInstitutionSelect.val() > 0) {
                    if(load_departments){
                        RoleChangeDepartmentSelect.select2("data", null, {allowClear: true}).load("/institutions/departments/" + RoleChangeInstitutionSelect.val()+"?include_other=0");
                    }
                }
                else {
                    function_clear_inst_admin_selections();
                }
            }

            function function_clear_inst_admin_selections(){
                RoleChangeInstitutionSelect.select2("data", null, {allowClear: true});
                RoleChangeDepartmentSelect.html('<option value=""></option>');
                RoleChangeDepartmentSelect.select2("data", null, {allowClear: true});
                RoleChangeDepartmentSelect.select2({
                    allowClear: true,
                    placeholder: "{!!trans('users.selectInstitutionFirst')!!}",
                });
            }

             @if($pop_role_change)
             $("#RequestRoleChangeModal").modal("show");
             @endif

            @else
            @if($pop_role_change)
            alert('{!! trans('site.roleChangeRequestDenied')!!}');
            @endif
            @endif
        });
    </script>
@endsection
@section('extra-css')

    <style>
        .container {
            min-width: 400px !important;
        }

        .box-padding {
            padding: 20px 30px;
        }
        .account_h{
           color:#777;
            font-weight:bold;
        }

        .table > tbody > tr > td {
            border-top: 0;
            border-bottom: 1px solid #DDD;
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
        .disabled_button{
            cursor: not-allowed;
        }
        @if(!$canBeDeleted)
            #DeleteAccountButton{
             cursor: not-allowed;
            }
       @endif
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
            <div class="box box-padding" style="margin-top:100px">
                <div class="row">
                    <div class="col-xs-7">
                        <h4 style="color:#52B6EC">{{trans('site.myAccount')}}</h4>
                    </div>

                    <div class="col-xs-5" style="text-align:right">
                        @if($canRequestRoleChange)
                            <button id="RoleChangeRequest" type="button" class="btn btn-info" data-toggle="tooltip"
                                    data-placement="bottom" title="{{trans('site.roleChangeRequest')}}"><i class="fa fa-file-text"
                                                                                                           style="font-size:18px"></i>
                            </button>
                        @else
                            <button type="button" class="btn btn-danger disabled_button" data-toggle="tooltip"
                                    data-placement="bottom" title="{{trans('site.roleChangeRequestDenied')}}"><i class="fa fa-file-text"
                                                                                                                 style="font-size:18px"></i>
                            </button>
                        @endif
                        <button id="ShowHistory" type="button" class="btn btn-info" data-toggle="tooltip"
                                data-placement="bottom" title="{{trans('site.confHistory')}}"><i class="fa fa-history"
                                                                                                 style="font-size:18px"></i>
                        </button>
                        <button id="ShowDetails" type="button" class="btn btn-info" data-toggle="tooltip"
                                data-placement="bottom" title="{{trans('site.editAccount')}}"><span
                                    class="glyphicon glyphicon-pencil"></span></button>

                        <button id="DeleteAccountButton" type="button" class="btn btn-danger" data-toggle="tooltip"
                                data-placement="bottom" title="@if(!$canBeDeleted) {{trans('requests.cantBeDeleted')}} @else{{trans('site.deleteAccount')}}@endif"><span
                                    class="glyphicon glyphicon-ban-circle"></span>
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <hr style="margin:10px 0px">
                        @if (session('message'))
                            <p class="alert alert-info" style="margin: 0px 15px 10px 15px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {{ session('message') }}
                            </p>
                        @endif
                        @if (session('error'))
                            <p class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {{ session('error') }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="well well-sm">
                    <div class="row">
                        <div class="col-sm-2 col-md-2">
                            @if(!empty($user->thumbnail))
                                <img src="/images/user_images/{{ $user->thumbnail }}" alt="thumbnail"
                                     class="img-responsive img-thumbnail"/>
                            @else
                                <span class="glyphicon glyphicon-user icon-size"></span>
                            @endif
                        </div>
                        <div class="col-sm-10 col-md-10" style="word-wrap:break-word">
                            <h4><strong>{{  $user->lastname }} {{  $user->firstname }}</strong> <i>
                                    <small> - {{  trans($user->roles->first()->label) }}</small>
                                </i></h4>
                            <p>
                                <span class="account_h">{{trans('users.primaryEmail')}}:</span>
                                {{ $user->email }}<br/>

                                @if((count($extra_emails['sso']) + count($extra_emails['custom'])) > 0 )
                                    <span style="color:#777; font-weight:bold;">{{trans('users.extraEmail')}}:</span>
                                @endif
                            @foreach($extra_emails['sso'] as $mail)
                                <div style="color:green;">
                                    {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
                                </div>
                            @endforeach
                            <div style="padding-bottom:7px;">
                                @foreach($extra_emails['custom'] as $mail)
                                    @if($mail['confirmed'] == 0)
                                        <div style="color:red;">
                                            {{$mail['email']}}
                                            ({{trans('users.customExtraMail')}} {{trans('users.emailNotConfirmedShort')}})
                                        </div>
                                    @else
                                        <div style="color:green;">
                                            {{$mail['email']}}
                                            ({{trans('users.customExtraMail')}} {{trans('users.emailConfirmedShort')}})
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            @if(!empty($user->telephone))
                                {{trans('site.tel')}}.: {{ $user->telephone }}<br/>
                            @endif
                            {{ $user->institutions->first()->title }}<br/>

                            @if($user->hasRole('EndUser') || $user->hasRole('DepartmentAdministrator'))
                                <i>
                                    <small> {{  $department->title }}</small>
                                </i>
                            @endif
                            <div class="col-md-12" style="margin-top:20px;">
                                <div class="btn-group" style="float:right;">
                                @if($user->state=="sso")
                                    <a href="/account/emails"><button type="button" class="btn btn-info" title="Διαχείριση Emails" style="margin-right:6px; margin-left:6px">{{trans('users.email_management')}}</button></a>
                                @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- MODALS--->
    @include('user_account_modals.conference_history_modal')
    @include('user_account_modals.edit_account_info_'.$user->state.'_modal')
    @include('user_account_modals.delete_anonymize_account_modal')
    @include('user_account_modals.request_role_change_modal')

    <div class="modal fade" id="termsConditions" tabindex="-1" data-focus-on="input:first" role="dialog" aria-labelledby="CoordDepartModalLabel" aria-hidden="true">
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
@endsection
