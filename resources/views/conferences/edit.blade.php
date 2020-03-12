@extends('app')

@section('header-javascript')

    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    @if(Session::get('locale') == 'el')
        <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    @endif
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

    <link rel='stylesheet' type='text/css' href='/timepicki/css/timepicki.css'/>

    <script type='text/javascript' src='/timepicki/js/timepicki.js'></script>

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">

    <script type="text/javascript" src="/js/bootstrap3-typeahead.js"></script>

    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>

    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">


    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript" src="/js/bootstrap3-typeahead.js"></script>


    <script type="text/javascript">
        function assignUserID(id) {
            var user = id;
            setTimeout(function () {
                $.post('/conferences/assign_participant', {
                    user_id: user,
                    conference_id: {{$conference->id}} })
                    .done(function (obj) {
                        if (obj.status === 'error') {
                            alert("" + obj.data);
                        } else if (obj.status === 'success') {
                            window.location.hash = '#ParticipatsBody';
                            window.location.reload(true);
                        }
                    })
                    .fail(function (xhr, textStatus, errorThrown) {
                        alert(xhr.responseText);
                    });
            }, 400);
        }

        function openModal() {
            $("#UserModal").modal("show");
            $(".select2-drop").css("display", "none");
            $("#FieldUserEmail").val($('#select2-drop .select2-search input').val());
            $("#select2-drop-mask").css("display", "none");
            var matched_error_container = $("#matched_error_container");
            var not_matched_error_container = $("#not_matched_error_container");


            var value = $("#FieldUserEmail").val();

            if (value.length > 4) {
                $.post("/users/check_mail_properties", {_token: "{{csrf_token()}}", mail: value})
                    .done(function (data) {
                        //obj = JSON.parse(data);
                        email_matched = data.matched;
                        email_checked = true;
                        update_warning_message();
                    });
            } else {
                email_checked = false;
                email_matched = false;
                matched_error_container.slideUp();
                not_matched_error_container.slideUp();
            }


        }

        function update_warning_message() {

            var local_state_checkbox = $("#FieldLocalState");
            var sso_state_checkbox = $("#FieldSsoState");

            var matched_error_container = $("#matched_error_container");
            var not_matched_error_container = $("#not_matched_error_container");

            if (email_checked) {
                if (local_state_checkbox.is(':checked') && email_matched) {
                    not_matched_error_container.slideUp();
                    matched_error_container.slideDown();

                } else if (sso_state_checkbox.is(':checked') && !email_matched) {
                    matched_error_container.slideUp();
                    not_matched_error_container.slideDown();
                } else {
                    matched_error_container.slideUp();
                    not_matched_error_container.slideUp();
                }
            } else {
                matched_error_container.slideUp();
                not_matched_error_container.slideUp();
            }
        }
    </script>

    @if($errors->isEmpty() == false && ($errors->has('firstname') || $errors->has('lastname') || $errors->has('email') || $errors->has('institution_id') || $errors->has('department_id') || $errors->has('new_department') || $errors->has('new_institution') || $errors->has('no_new_org') || $errors->has('state')));

    <script>
        $(document).ready(function () {
            $("#UserModal").modal("show");
        });
    </script>

    @endif

    <script>


        function update_send_notifications_button() {

            var checked_count = $(".check:checked").length;
            var total_count = $(".check").length;

            if (checked_count > 0)
                $("#SendParticipantsEmail").attr("disabled", false);
            else
                $("#SendParticipantsEmail").attr("disabled", true);


            if (checked_count === total_count)
                $("#checkAll").prop('checked', true);


            if (checked_count === 0)
                $("#checkAll").prop('checked', false);

        }


        $(document).ready(function () {

            // ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ participantsTable

            //  Ορισμός Πίνακα DataTable - {"bVisible": false} για να κρύψουμε πχ rec-no
            $("#checkAll").click(function () {
                $(".check").prop('checked', $(this).prop('checked'));
                update_send_notifications_button();

            });

            //Update button status on load and on checkbox value change

            update_send_notifications_button();

            $(".check").on("change", function () {
                update_send_notifications_button();
            });


            //Update button status on load and on checkbox value change

            var new_mail_field = $("#FieldUserEmail");
            var value = null;

            var matched_error_container = $("#matched_error_container");
            var not_matched_error_container = $("#not_matched_error_container");

            email_matched = false;
            email_checked = false;

            //Check if the domain of the emails typed is in the list of the organizations domains

            new_mail_field.on("keyup", function () {
                value = new_mail_field.val();

                if (value.length > 4) {
                    $.post("/users/check_mail_properties", {_token: "{{csrf_token()}}", mail: value})
                        .done(function (data) {
                            //obj = JSON.parse(data);
                            email_matched = data.matched;
                            email_checked = true;
                            update_warning_message();
                        });
                } else {
                    email_checked = false;
                    email_matched = false;
                    matched_error_container.slideUp();
                    not_matched_error_container.slideUp();
                }
            });

            //Check again on radio button change

            $(".user_state_radio_button").on("change", function () {
                update_warning_message();
            });

            $("#FieldUserDepart").select2({allowClear: true});

            $("#UserOrgNew").hide();
            $("#UserDepartNew").hide();

            //Load departments for Φυσικό πρόσωπο
            $("#FieldUserOrg").change(function () {
                if ($("#FieldUserOrg").val() == "other") {
                    $("#UserOrgNew").show();
                    $("#UserDepartNew").show();
                    $("#FieldUserDepart").val(0);
                    $("#UserDepart").hide();
                } else {
                    $("#UserOrgNew").hide();
                    $("#UserDepartNew").hide();
                    $("#UserDepart").show();
                    $("#FieldUserDepart").load("/institutions/departments/" + $("#FieldUserOrg").val());
                }
            });

            $("#FieldUserDepart").on("change", function () {
                if ($("#FieldUserDepart").val() == "other") {
                    $("#UserDepartNew").show();
                } else if ($("#FieldUserDepart").val() != "other" && $("#FieldUserDepart").val() > 0) {
                    $("#UserDepartNew").hide();
                } else if ($("#FieldUserDepart").val() == "") {
                    $("#UserDepartNew").hide();
                }
            }).trigger("change");

            // για ελεγχο αποστολής μηνύματος ενεργοποιησης/απενεργγοποιησης ΦΠ

            $("#FieldUserOrg").select2({
                allowClear: true,
                placeholder: "{!!trans('conferences.selectInstitution')!!}"
            }).on("change", function () {
                if ($("#FieldUserOrg").val() > 0) {
                    $("#FieldUserDepart").select2("data", null, {allowClear: true}).load("/institutions/departments/" + $("#FieldUserOrg").val());
                } else {
                    $("#FieldUserDepart").select2({
                        placeholder: "{!!trans('conferences.selectInstitutionFirst')!!}",
                        allowClear: true
                    });
                }
            }).trigger("change");

            // Close Button στο modal ΦΠ
            $("#UserModalButtonClose").click(function () {
                $("#UserModal").modal("hide");
                $("#UserModal .alert").hide();
            });

            $('#UserModal').on('hidden.bs.modal', function () {
                $("#UserModal .alert").hide();
            });

            $("#SendParticipantsEmail").on("click", function (event) {
                // disable unload warning
                $(window).off('beforeunload');
            });

            $('.summernote').summernote({
                lang: 'el-GR',
                height: 50,
                toolbar: [
                    // [groupName, [list of button]]
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['font', ['strikethrough', 'superscript', 'subscript']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['height', ['height']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                ]
            });

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

            $('[data-toggle="tooltip"]').tooltip();


// ΛΕΠΤΟΜΕΡΕΙΕΣ ΤΗΛΕΔΙΑΣΚΕΨΗΣ

            $("#TeleDetailsAlert").hide();
            $("#TeleSaveDetails").hide();
            $("#ParticipatsTitle").show();
            $("#ParticipatsBody").show();
            $("#ParticipatsBody").show();
            $("#ExitFromPageDiv").hide();


            // $('#FieldStartTime').on('change', function(){
            // var start = $(this).val();
            // var new_endtime = start.substr(0, 3);
            // alert(new_endtime);
            // });

            $('.clockpicker').clockpicker();

            $("#CreateUserButton").hide();


            $("#TeleSave").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    window.location.href = "/conferences";
                }, 800);
            });

            $("#TeleSaveDetails").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    $("#TeleDetailsAlert").hide();
                }, 700);
            });

            $("#TeleSaveAndAddUsers").click(function () {
                // ελέγχους και αν όλα καλά..
                $("#TeleDetailsAlert").show();
                setTimeout(function () {
                    $("#TeleDetailsAlert").hide();
                    $("#TeleInitialSaveGroupButtons").hide();
                    $("#TeleReturn").hide();
                    $("#TeleSaveDetails").show();
                    $("#TeleTile").text("{!!trans('conferences.conferenceDetails')!!}");
                    $("#ParticipatsTitle").show();
                    $("#ParticipatsBody").show();
                    $("#ExitFromPageDiv").show();
                }, 800);
            });

            $("#GotoTop").click(function () {
                var gotopage = $("#ParticipatsTitle").offset().top;
                $("html, body").animate({scrollTop: gotopage});
            });

            $('#FieldStartDate').on('change', function () {
                var hdate = $('#FieldStartDate').val();
                if ($('#FieldEndDate').val() == "") {
                    $('#FieldEndDate').val(hdate);
                }
            });

            $("#AddPUserButtonNone").click(function () {
                $("#AddPUserModal").modal("show");
            });

            $("#CreateUserButton").on("click", function () {
                $("#UserModal").modal("show");
            });




            $("[id^=openParticipantDetails]").on("click", function () {
                var user = $(this).attr('id').split('-').pop(-1);
                if ($("#participantDetails-" + user).hasClass("out")) {
                    $("#participantDetails-" + user).addClass("in");
                    $("#participantDetails-" + user).removeClass("out");
                } else if ($("#participantDetails-" + user).hasClass("in")) {
                    $("#participantDetails-" + user).addClass("out");
                    $("#participantDetails-" + user).removeClass("in");
                } else {
                    $("#participantDetails-" + user).addClass("in");
                }
            });

            function format(state) {
                if (!state.id) return state.text; // optgroup
                if (state.text == 1) return "<span class='glyphicon glyphicon-ok'></span>";
                if (state.text == 0) return "<span class='glyphicon glyphicon-ban-circle'></span>";
            }


            // Sort table

            // Get url parameters
            $.urlParam = function (name) {
                var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
                return results[1] || 0;
            }

            // Default class
            var sortings = ["lastname", "email", "state"];
            var url = window.location.href;
            $.each(sortings, function (index, value) {
                if (url.search("sort_" + value) > 0 && value != "lastname") {
                    $("#sort_" + value).removeClass("sorting");
                    $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
                    $("#sort_lastname").removeClass("sortingasc");
                    $("#sort_lastname").addClass("sorting");
                } else if (url.search("sort_" + value) > 0 && value == "lastname") {
                    $("#sort_" + value).removeClass("sortingdesc");
                    $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
                }
            });


            $("[id^=sort]").on("click", function () {
                var sortings = ["lastname", "email", "state"];
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
                    window.location.assign(url_pathname + "?" + current_param + $.param(params) + "#ParticipatsBody");
                } else if (url.search("sort_" + col) > 0) {
                    var variable = $.urlParam("sort_" + col);
                    var new_var = "desc";
                    if (variable === "desc") {
                        var new_var = "asc";
                    }
                    $.each(sortings, function (index, value) {
                        current_param = current_param.replace("&sort_" + value + "=asc", "");
                        current_param = current_param.replace("&sort_" + value + "=desc", "");
                    });
                    current_param = current_param.replace("&sort_" + col + "=" + variable, "");
                    var params = [{name: "sort_" + col, value: new_var}];
                    window.location.assign(url_pathname + "?" + current_param + $.param(params) + "#ParticipatsBody");
                }
            });


            $("#AddPUserButton").click(function () {
                $("#AddPUserModal").modal("show");
            });

            $("[id^=ParticipantBtnDelete]").on("click", function () {
                var row = $(this).closest('tr');
                var nRow = row[0];
                var user = $(this).attr('id').split('-').pop(-1);
                var r = confirm("{!!trans('conferences.questionRemoveParticipant')!!}");
                if (r == true) {
                    $.post('/conferences/detach_participant', {
                        conference_id: {{$conference->id}},
                        user_id: user,
                        _token: '{{csrf_token()}}'
                    })
                        .done(function (msg) {

                            if (msg.status === 'true') {
                                location.reload();
                            } else {
                                console.log(msg.message);
                            }

                        })
                        .fail(function (xhr, status, error) {
                            console.log(xhr);
                        });
                }
            });

// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL

            $('#FieldInvMessage').summernote({
                lang: 'el-GR'
            });

            $("#InvEmail").click(function () {
                $("#InvEmailMessage").hide();
                $("#InvEmailModal").modal("show");
            });

            $("#InvEmailSubmitBtn").click(function () {
                // αποστολή email...και μετά από ελέγχους αποστολής το ανάλογο μύνημα
                $("#InvEmailMessage").text("{!!trans('conferences.emailSent')!!}");
                $("#InvEmailMessage").removeClass("alert-danger").addClass("alert-info");
                $("#InvEmailMessage").show();
                window.setTimeout(function () {
                    $("#InvEmailModal").modal("hide");
                }, 500);
            });

// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ USERS DATATABLES START

            var uTable = $("#UsersExample").dataTable({
                "bSort": false,
                "bInfo": false,
                "bPaginate": false,
                "oLanguage": {
                    "sZeroRecords": "{{trans('conferences.noUsersFound')}}",
                    "sInfoFiltered": "({{trans('conferences.fromNoUsers')}})",
                },
                "aoColumns": [
                    {"sClass": "cellName"},
                    {"sClass": "cellRole"},
                    {"sClass": "cellOrg hidden-xs"},
                    {"sClass": "cellDepart hidden-xs"},
                    {"sClass": "cellButton"}
                ]
            });

            // var uTable = $("#UsersExample").dataTable();

            function changeDisplayLength(uTable, iDisplayLength) {
                var oSettings = uTable.fnSettings();
                oSettings._iDisplayLength = iDisplayLength;
                uTable.fnDraw();
            }

            $("#datatablesChangeDisplayLength").change(function () {
                changeDisplayLength(uTable, +($(this).val()));
            });

            $("#datatablesSearchTextField").keyup(function () {
                uTable.fnFilter($(this).val());
            });


            // φιλτρα πάνω απο columns
            $("#selectColRole").select2({
                allowClear: true,
                containerCssClass: "select2-container-sm",
                dropdownCssClass: "tpx-select2-drop"
            });

            $("#selectColType").select2({
                allowClear: true,
                containerCssClass: "select2-container-sm",
                dropdownCssClass: "tpx-select2-drop"
            });

            $("#selectColStatus").select2({
                allowClear: true,
                containerCssClass: "select2-container-sm",
                dropdownCssClass: "tpx-select2-drop"
            });

            // φιλτρα σε columns
            $('#selectColName').on('keyup change', function () {
                uTable.fnFilter($(this).val(), 0);
            });

            $('#selectColRole').on('change', function () {
                var selected = $(this).val();
                uTable.fnFilter(selected, 1);
            });

            $('#selectColEmail').on('keyup change', function () {
                uTable.fnFilter($(this).val(), 2);
            });

            $('#selectColOrg').on('keyup change', function () {
                uTable.fnFilter($(this).val(), 3);
            });

            $('#selectColDepart').on('keyup change', function () {
                uTable.fnFilter($(this).val(), 4);
            });

            $('#selectColType').on('change', function () {
                uTable.fnFilter($(this).val(), 5);
            });

            $('#selectColStatus').on('change', function () {
                uTable.fnFilter($(this).val(), 6);
            });

            $("#UClearFilter").click(function () {
                $("[id^=selectCol], input[type=text]").val(null);
                $("[id^=selectCol], input[type=select]").select2("val", null);
                var oSettings = uTable.fnSettings();
                for (iCol = 0; iCol < oSettings.aoPreSearchCols.length; iCol++) {
                    oSettings.aoPreSearchCols[iCol].sSearch = '';
                }
                uTable.fnDraw();
            });


// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ USERS DATATABLES END


            $("#datatablesSearchEmailFieldSelect").select2("val", null);

            $("#datatablesSearchEmailFieldSelect").select2({
                placeholder: '{!!trans("conferences.typeUserEmail")!!}',
                minimumInputLength: 5,
                ajax: {
                    url: "/conferences/conferenceAddUserEmail",
                    dataType: 'json',
                    type: "POST",
                    quietMillis: 50,
                    data: function (term, page) { // page is the one-based page number tracked by Select2
                        return {
                            email: term, //search term
                            page: page // page number
                        };
                    },
                    results: function (data, page) {
                        var more = (page * 30) < data.total_count; // whether or not there are more results available

                        // notice we return the value of more so Select2 knows if more results can be loaded
                        return {results: data, more: more};
                    }
                },
                allowClear: true,
                formatNoMatches: function () {
                    return '{!!trans("conferences.noUsersFoundEmail")!!} <button type="button" class="btn btn-success" onclick="openModal()"><small> <span class="glyphicon glyphicon-plus-sign"></span> {{trans("conferences.createNewUser")}}</small></button>';
                }
            });
            $("#datatablesSearchEmailFieldSelect").on("change", function () {
                uTable.fnDestroy();
                var zTable = $("#UsersExample").dataTable({
                    "bSort": false,
                    "bInfo": false,
                    "bPaginate": false,
                    "bProcessing": true,
                    "bServerSide": true,
                    "sAjaxSource": "/conferences/requestParticipant/" + $("#datatablesSearchEmailFieldSelect").val(),
                    "oLanguage": {
                        "sZeroRecords": "{{trans('conferences.noUsersFound')}}",
                        "sInfoFiltered": "({{trans('conferences.fromNoUsers')}})",
                    },
                    "aoColumns": [
                        {"sClass": "cellName"},
                        {"sClass": "cellRole"},
                        {"sClass": "cellOrg hidden-xs"},
                        {"sClass": "cellDepart hidden-xs"},
                        {"sClass": "cellButton"}
                    ]
                });

                function changeDisplayLength(zTable, iDisplayLength) {
                    var oSettings = zTable.fnSettings();
                    oSettings._iDisplayLength = iDisplayLength;
                    zTable.fnDraw();
                }

                $("#datatablesChangeDisplayLength").change(function () {
                    changeDisplayLength(zTable, +($(this).val()));
                });

                $("#datatablesSearchTextField").keyup(function () {
                    zTable.fnFilter($(this).val());
                });


                $('[id^=RowAddtoTele]').css('cursor', 'default');

                $('[id^=RowAddtoTele]').mousedown(function (event) {
                    event.preventDefault();
                });

            });

            $('#FieldStartTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["{{(string)strstr($conference->getTime($conference->start), ':', true)}}", "{{(string)substr($conference->getTime($conference->start), 3)}}"]
            });

            $('#FieldEndTime').timepicki({
                show_meridian: false,
                min_hour_value: 0,
                max_hour_value: 23,
                step_size_minutes: 15,
                overflow_minutes: true,
                increase_direction: 'up',
                disable_keyboard_mobile: true,
                start_time: ["{{(string)strstr($conference->getTime($conference->end), ':', true)}}", "{{(string)substr($conference->getTime($conference->end), 3)}}"]
            });


            $('#FieldStartDate').datepicker({
                format: "dd-mm-yyyy",
                todayBtn: "linked",
                language: "el",
                autoclose: true,
                todayHighlight: true
            });

            $('#FieldEndDate').datepicker({
                format: "dd-mm-yyyy",
                todayBtn: "linked",
                language: "el",
                autoclose: true,
                todayHighlight: true
            });


            $('#FieldStartDate').datepicker().on('changeDate', function (e) {
                startDate = e.date;
                $('#FieldEndDate').datepicker('setDate', e.date);
            });

            $('#FieldEndDate').datepicker().on('changeDate', function (e) {

                if (e.date >= startDate)
                    endDate = e.date;
                else
                    $('#FieldEndDate').datepicker('setDate', startDate);

            });

            startDate = $('#FieldStartDate').datepicker('getDate');
            endDate = $('#FieldEndDate').datepicker('getDate');

            var ShoursStart = {{(string)strstr($conference->getTime($conference->start), ':', true)}};
            var SminutesStart = {{(string)substr($conference->getTime($conference->start), 3)}};

            var startTime = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate(), ShoursStart, SminutesStart, 0, 0);


            var EhoursStart = {{(string)strstr($conference->getTime($conference->end), ':', true)}};
            var EminutesStart = {{(string)substr($conference->getTime($conference->end), 3)}};

            var endTime = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate(), EhoursStart, EminutesStart, 0, 0);


            diff = Math.abs(endTime - startTime);


        });
    </script>
@endsection
@section('extra-css')
    <style>

        #matched_error_container {
            display: none;
        }

        #not_matched_error_container {
            display: none;
        }

        .container {
            min-width: 550px !important;
        }

        .zero-width {
            display: none;
            width: 0px;
        }

        table {
            width: 100% !important;
        }

        table#participantsTable th {
            font-size: 12px;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
        }

        table#participantsTable td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            width: 10px;
            min-width: 10px;
            max-width: 10px;
        }

        table#participantsTable thead .sorting:before {
            font-family: 'Glyphicons Halflings';
            content: "\e150";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table#participantsTable thead .sortingasc:before {
            font-family: 'Glyphicons Halflings';
            content: "\e155";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table#participantsTable thead .sortingdesc:before {
            font-family: 'Glyphicons Halflings';
            content: "\e156";
            padding: 0px 2px;
            font-size: 0.8em;
            color: #52b6ec;
        }

        table#participantsTable thead .sorting, table thead .sortingasc, table thead .sortingdesc {
            cursor: pointer;
        }

        .participant_details {
            cursor: pointer;
        }

        .hiddenRow {
            padding: 0 !important;
        }

        .cellDetails {
            width: 20px !important;
        }

        .cellPCheck {
            width: 20px !important;
        }

        .cellPName {
            width: 100px !important;
        }

        .cellPEmail {
            width: 80px !important;
        }

        .cellPState {
            width: 20px !important;
        }

        .cellPDevice {
            width: 120px !important;
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

        .dataTables_processing {
            display: none;
        }

        /* CLASSES FOR USERS DATATABLE START */
        table#UsersExample td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
            max-width: 10px;
        }

        .cellName {
            width: 255px !important;
        }

        .cellPSendEmail {
            width: 50px !important;
        }

        .cellPConfirm {
            width: 50px !important;
        }

        .cellEmail {
            width: 255px !important;
        }

        .cellOrg {
            width: 170px !important;
        }

        .cellDepart {
            width: 120px !important;
        }

        .cellDevice {
            width: 70px !important;
        }

        .cellStatus {
            width: 80px !important;
        }

        .cellButton {
            width: 170px !important;
        }

        .hidden {
            display: none;
        }

        /* CLASSES FOR USERS DATATABLE END */


    </style>
@endsection

@section('conference-active')
    class = "active"
@endsection

@section('content')
    <section id="vroom">
        <div class="container">

            <!-- Form Details -START -->
            <div class="box" style="padding:0px; background-color:transparent; margin-top:100px">
                <h4 id="TeleTile">{{trans('conferences.conference')}}</h4>
            </div>
            <div class="box" style="padding:30px 30px  20px 30px">
                <div class="row" style="margin:0px;">
                    @if(session('storesSuccessfullyRecurrent'))
                        <div class="alert alert-info" role="alert" style="margin: 0 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {!! session('storesSuccessfullyRecurrent') !!}
                        </div>
                    @else
                        @if (session('storesSuccessfully'))
                            <div class="alert alert-info" role="alert" style="margin: 0 15px 10px 15px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {!! session('storesSuccessfully') !!}
                            </div>
                        @endif
                    @endif
                    @if (session('message'))
                        <div class="alert alert-info">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ session('message') }}
                        </div>
                    @endif
                    @if($errors->isEmpty() == false && ($errors->has('firstname') == false && $errors->has('lastname') == false && $errors->has('email')  == false && $errors->has('institution_id') == false && $errors->has('department_id') == false && $errors->has('new_department') == false && $errors->has('new_institution') == false && $errors->has('no_new_org') == false) && $errors->has('state') == false)
                        <ul class="alert alert-danger" style="margin: 0px 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    {!! Form::model($conference, array('url' => ['conferences/' . $conference->id], 'method' => 'PATCH', 'class' => 'form-horizontal', 'id' => 'OrgForm', 'role' => 'form')) !!}
                    @include('conferences._form', ['start_date' => $conference->getDate($conference->start), 'start_time' => $conference->getTime($conference->start), 'end_date' => $conference->getDate($conference->end), 'end_time' => $conference->getTime($conference->end), 'max_duration' => null, 'max_users' => null, 'max_h323' => null, 'max_vidyo_room' => null, 'invisible' => Form::getValueAttribute('invisible'), 'submitBtn' => trans('conferences.saveConference'), 'copyBtn' => '<a href="/conferences/'.$conference->id.'/copy"><button type="button" class="btn btn-warning btn-sm" id="TeleCopy" >'.trans('conferences.conferenceCopy').'</button></a>'])
                    {!! Form::close() !!}
                </div>
            </div><!--/.box-->
            <div class="small-gap"></div>
        @if($errors->isEmpty() || $errors->has('firstname') || $errors->has('lastname') || $errors->has('email') || $errors->has('institution_id') || $errors->has('department_id') || $errors->has('new_department') || $errors->has('new_institution')|| $errors->has('no_new_org') || $errors->has('state'))
                <div id="ParticipatsTitle" class="box" style="padding:0; background-color:transparent;">
                    <h4>
                        {{trans('conferences.participantList')}}
                    </h4>
                </div>
                <div class="box" id="ParticipatsBody">
                    @if (session('error'))
                        <div class="alert alert-danger">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ session('error') }}
                        </div>
                    @endif
                    <div class="row well" id="addParticipantTable">
                        <h4>{{trans('conferences.addParticipants')}} <span class="glyphicon glyphicon-share-alt"></span>
                        </h4>
                        <hr>
                        <div class="row"> <!-- Row with search field and add button - START -->
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="input-group pull-left" style="width:400px">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-search"></i></span>
                                    <input type="hidden" id="datatablesSearchEmailFieldSelect" value=""
                                           style="width: 100%;"/>
                                </div>
                            </div>
                        </div> <!-- Row with search field and add button - END -->
                        @include('conferences.add_multiple_participants_row')
                        <table style="margin-top:10px; width:100%" class="table table-hover table-striped table-bordered" id="UsersExample">
                            <thead>
                            <tr>
                                <th>{{trans('conferences.fullName')}}</th>
                                <th>{{trans('conferences.userType')}}</th>
                                <th>{{trans('conferences.institution')}}</th>
                                <th>{{trans('conferences.department')}}</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tfoot style="background-color:#b0b0b0">
                            </tfoot>
                        </table>
                    </div>
                    <!-- DATA TABLES START -->
                    {!! Form::open(array('url' => 'conferences/sendParticipantEmail', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'sendParticipantEmail', 'role' => 'form')) !!}
                    <div class="small-gap"></div>
                    @if(count($conference->participants) == 0)
                        <div class="alert alert-danger">
                            <strong>{{trans('conferences.noParticipantsYet')}}</strong>
                        </div>
                    @else
                        @include('conferences._participantsTable', ['sort' => Input::get()])
                    @endif
                    {!! Form::hidden('conference_id', $conference->id) !!}
                    <div class="small-gap"></div>
                    <div class="row">
                        <div class=" col-md-12 col-sm-12 col-xs-12" style="text-align:right">
                            <div>
                                {!! Form::button('<span class="glyphicon glyphicon-envelope"></span> '.trans('conferences.sendInvite1'), ['class' => 'btn btn-primary', 'id' => 'SendParticipantsEmail', 'type' => 'submit', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => trans('conferences.sendInvite2'),'disabled'=>true]) !!}
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}
                </div>
            @endif
            <div class="small-gap"></div>
            <div id="ExitFromPageDiv" class="box" style="padding:0; background-color:transparent;">
                <button type="button" class="btn pull-right btn-default"
                        id="ExitFromPage"> {{trans('conferences.return')}}</button>
            </div>
        </div><!--/.container-->
        <!-- MODAL User start -->
        <div class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-labelledby="UserModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="UserLabel">{{trans('conferences.createNewUser')}}</h4>
                    </div> <!-- .modal-header -->
                    <div class="small-gap"></div>
                    @if($errors->isEmpty() == false &&
                        ($errors->has('firstname') ||
                         $errors->has('lastname') ||
                         $errors->has('email')  ||
                         $errors->has('institution_id') ||
                         $errors->has('department_id') ||
                         $errors->has('new_department') ||
                         $errors->has('new_institution') ||
                         $errors->has('no_new_org') ||
                         $errors->has('state'))
                        )
                        <ul class="alert alert-danger" style="margin:0 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            @foreach($errors->all() as $error)
                                <li>{!! $error !!}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="modal-body">
                        {!! Form::open(array('url' => 'users', 'method' => 'post', 'class' => 'form-horizontal', 'id' => 'CoordOrgForm', 'role' => 'form')) !!}
                        <div class="form-group">
                            {!! Form::label('FieldUserEmail', 'Email:', ['class' => 'control-label col-sm-4 ']) !!}
                            <div class="col-sm-8">
                                {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Email ('.trans('conferences.required').')', 'id' => 'FieldUserEmail']) !!}
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        {!! Form::hidden('conference_id', $conference->id) !!}
                        {!! Form::hidden('specialUser', 'conferenceUser') !!}
                        {!! Form::hidden('from', URL::full()) !!}
                    </div> <!-- .modal-body -->
                    <div class="modal-footer" style="margin-top:0;">
                        {!! Form::submit(trans('conferences.saveUserAddParticipant'), ['class' => 'btn btn-primary', 'id' => 'UserSubmitBtnNew', 'name' => 'conferenceAddNewUser']) !!}
                        <button type="button" id="UserModalButtonClose" class="btn btn-default">{{trans('conferences.cancel')}}</button>
                    </div> <!-- .modal-footer -->
                </div> <!-- .modal-content -->
                {!! Form::close() !!}
            </div> <!-- .modal-dialog -->
        </div> <!-- .modal -->
        <!-- modal User end -->

    </section>
@endsection
