@extends('app')

@section('header-javascript')
    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>
    <script src="/clipboard/clipboard.min.js"></script>

    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="/css/font-awesome.css">

    <!-- JS Countdown -->
    <script src="/js_countdown/jquery.simple.timer.js"></script>

    <!-- Checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">

    <!-- bootstrap date-picker    -->
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>

    <link rel="stylesheet" href="/select2/select2-small.css">

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">

        var user_id = '{{$authenticated_user->id}}';
        var channel = 'conference-user-' + user_id;

        Echo.private(channel)
            .listen('.participant-status-changed', function (data) {
                if (data.status === "1") {
                    flip_join_conference_button_status(data.conference_id, "enable", "participant_disabled");
                } else if (data.status === "0") {
                    flip_join_conference_button_status(data.conference_id, "disable", "participant_disabled");
                }

            });


        Echo.private(channel)

            .listen('.participant-device-changed', function (data) {
                var device_string = '';

                if (data.type === "active") {
                    var conference_button = $("#GotoTele-" + data.conference_id);
                    device_string = $("#ActiveConferenceDevice-" + data.conference_id);
                    conference_button.attr("data-device", data.device);
                } else {
                    device_string = $("#FutureConferenceDevice-" + data.conference_id);
                }

                device_string.html(data.device);
            });


        Echo.private(channel)
            .listen('.conference-ended', function (data) {

                if (data.type === "active") {

                    $("#ActiveConferenceRow-" + data.conference_id).remove();

                    var active_conferences = $("[id^=ActiveConferenceRow]").length;

                    if (active_conferences === 0) {
                        $("#activeConferencesTable").remove();
                        $("#no_active_conferences_message").show();
                    }

                } else {

                    $("#FutureConferenceRow-" + data.conference_id).remove();

                    var future_conferences = $("[id^=FutureConferenceRow]").length;

                    if (future_conferences === 0) {
                        $("#futureConferencesTable").remove();
                        $("#no_future_conferences_message").show();
                    }

                }

            });


        Echo.private(channel)
            .listen('.conference-lock-status-changed', function (data) {

                if (data.status === "locked")
                    flip_join_conference_button_status(data.conference_id, "disable", "conference_lock");
                else
                    flip_join_conference_button_status(data.conference_id, "enable", "conference_lock");

            });


        Echo.private(channel)
            .listen('.participant-added', function (data) {

                //Update ui for new active conference

                switch (data.type) {

                    case "active":
                        reload_active_conferences();
                        break;

                    case "future":
                        reload_future_conferences();
                        break;
                }

            });


        Echo.private(channel)
            .listen('.participant-removed', function (data) {
                //Update ui for new active conference

                switch (data.type) {

                    case "active":
                        reload_active_conferences();
                        break;

                    case "future":
                        reload_future_conferences();
                        break;
                }

                // $("#ActiveConferenceRow-" + data.conference_id).remove();
                //
                // var active_conferences = $(".GotoTele").length;
                //
                // if (active_conferences === 0) {
                //     $("#no_active_conferences_message").show();
                // }
            });


        Echo.private(channel)
            .listen('.conference-created', function (data) {

                //Update ui for new active conference

                switch (data.type) {

                    case "active":
                        reload_active_conferences();
                        break;

                    case "future":
                        reload_future_conferences();
                        break;
                }

            });

        Echo.private(channel)
            .listen('.conference-enabled', function (data) {

                //Update ui when a future conference is activated

                reload_active_conferences();

                reload_future_conferences();
            });


        Echo.private(channel)
            .listen('.conference-details-changed', function (data) {
                //Update ui when a future conference is activated

                var fields_updated = data.fields_updated;

                for (var property in fields_updated) {
                    if (fields_updated.hasOwnProperty(property)) {

                        if (data.type === "future") {

                            switch (property) {

                                case "start":
                                    $("#FutureConferenceStartDate-" + data.conference.id).html(fields_updated[property]);
                                    break;

                                case "title":
                                    $("#FutureConferenceTitle-" + data.conference.id).html(fields_updated[property]);
                                    break;

                                case "end":
                                    $("#FutureConferenceEndTime-" + data.conference.id).html(fields_updated[property]);
                                    break;

                            }
                        } else {

                            switch (property) {

                                case "end":
                                    $("#ActiveConferenceEndTime-" + data.conference.id).html(fields_updated[property]);
                                    break;

                                case "title":
                                    $("#ActiveConferenceTitle-" + data.conference.id).html(fields_updated[property]);
                                    break;

                            }
                        }

                    }
                }

            });


        function reload_active_conferences() {

            $.get("/conferences/get_active_conferences_container_ajax", function () {
            })
                .done(function (data) {
                    $("#active_conferences_container").html(data);
                    initTooltips();
                })
        }


        function reload_future_conferences() {

            $.get("/conferences/get_future_conferences_container_ajax", function () {
            })
                .done(function (data) {
                    $("#future_conferences_container").html(data);
                    initTooltips();
                })
        }

        function flip_join_conference_button_status(conference_id, action, type) {

            var disabled_text = '{{trans('application.inactive')}}';
            var locked_text = '{{ trans('conferences.conference_room_locked') }}';


            var conference_button = $("#GotoTele-" + conference_id);
            var status_string = $("#status-" + conference_id);

            if (action === "disable") {

                conference_button.removeClass('btn-success');
                conference_button.addClass('btn-danger');

                switch (type) {
                    case "conference_lock":
                        status_string.html(locked_text);
                        break;
                    case "participant_disabled":
                        status_string.html(disabled_text);
                        break;
                }
                conference_button.attr("disabled", true);

            } else {
                //Need to check when conference is unlocked if user is disabled - if yes we shouldn't enable the button
                reload_active_conferences();
                reload_future_conferences();
                //conference_button.attr("disabled", false);
            }
        }


        function initTooltips() {
            $('[data-toggle="tooltip"]').tooltip();
            $('[data-toggle="popover"]').popover();
        }


        $(document).ready(function () {

            // ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

            initTooltips();


            @if($authenticated_user->hasRole('SuperAdmin'))
            $('#datatablesSearchDateField').on('change', function () {
                var url = $(this).val(); // get selected value
                if (url) { // require a URL
                    window.location = "/conferences/date/" + url + "?sort_start=asc&limit=50"; // redirect
                }
                return false;
            });
            @else
            $('#datatablesSearchDateField').on('change', function () {
                var url = $(this).val(); // get selected value
                if (url) {
                    // require a URL
                    // redirect
                    window.location = "/conferences/date/" + url;
                }
                return false;
            });
            @endif


            // Close H323 Modal
            $("alreadyConnectedButtonClose").click(function () {
                $("alreadyConnected").modal("hide");
            });

            // FieldH323SIP

            $(document).on("click", '[id^=FieldH323SIP]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                if ($(this).is(":checked")) {
                    // Disable your roomnumber element here
                    $("#FieldH323Encryption-" + conference).prop("disabled", true);
                    $("#FieldH323Compatibility-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323HD-" + conference).is(":checked") == true) {
                    // Disable your roomnumber element here
                    $("#FieldH323Encryption-" + conference).prop("disabled", false);
                    $("#FieldH323Compatibility-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323HD-" + conference).is(":checked") == false) {
                    $("#FieldH323Encryption-" + conference).prop("disabled", false);
                    $("#FieldH323Compatibility-" + conference).prop("disabled", false);
                }
            });

            // FieldH323Compatibility

            $(document).on("click", '[id^=FieldH323Compatibility]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                allow = $("#FieldH323AllowHD-" + conference).val();
                if ($(this).is(":checked")) {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", true);
                    $("#FieldH323HD-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323Encryption-" + conference).is(":checked") == true && allow == "allowed") {
                    // Disable your roomnumber element here
                    $("#FieldH323HD-" + conference).prop("disabled", false);
                    $("#FieldH323SIP-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323Encryption-" + conference).is(":checked") == true && allow == "notAllowed") {
                    // Disable your roomnumber element here
                    $("#FieldH323HD-" + conference).prop("disabled", true);
                    $("#FieldH323SIP-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323Encryption-" + conference).is(":checked") == false && allow == "allowed") {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", false);
                    $("#FieldH323HD-" + conference).prop("disabled", false);
                } else if ($(this).is(":checked") == false && $("#FieldH323Encryption-" + conference).is(":checked") == false && allow == "notAllowed") {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", false);
                    $("#FieldH323HD-" + conference).prop("disabled", true);
                }
            });

            // FieldH323Encryption

            $(document).on("click", '[id^=FieldH323Encryption]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                if ($(this).is(":checked")) {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323Compatibility-" + conference).is(":checked") == true) {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323Compatibility-" + conference).is(":checked") == false) {
                    // Disable your roomnumber element here
                    $("#FieldH323SIP-" + conference).prop("disabled", false);
                }
            });

            // FieldH323HD
            $(document).on("click", '[id^=FieldH323HD]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                if ($(this).is(":checked")) {
                    // Disable your roomnumber element here
                    $("#FieldH323Compatibility-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323SIP-" + conference).is(":checked") == true) {
                    // Disable your roomnumber element here
                    $("#FieldH323Compatibility-" + conference).prop("disabled", true);
                } else if ($(this).is(":checked") == false && $("#FieldH323SIP-" + conference).is(":checked") == false) {
                    // Disable your roomnumber element here
                    $("#FieldH323Compatibility-" + conference).prop("disabled", false);
                }
            });



            //Go to conference methods 

            // Button Desktop-Mobile



            $(document).on("click", '[id^=mobileLinuxMessageButtonClose]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                $("#mobileLinuxMessage-" + conference).modal("hide");
            });

            $(document).on("click", '[id^=mobileLinuxConnectID]', function () {

                var conference = $(this).attr('id').split('-').pop(-1);
                $("#mobileLinuxMessage-" + conference).modal("hide");
                window.location.assign("/conferences/" + conference + "/conferenceConnection");
                window.open("/conferences/" + conference + "/join_conference_mobile", "_blank");
            });

            // Close H323 Modal

            $(document).on("click", '[id^=H323ModalButtonClose]', function () {
                var conference_id = $(this).attr('id').split('-').pop(-1);
                var step_two_ele = $("#H323-step-two-"+conference_id);

                $("#H323Modal-" + conference_id).modal("hide");
                $("#H323-step-one-errors-" + conference_id).html('');

                if(step_two_ele.css('display') !== "none") {
                    step_two_ele.hide();
                    $("#H323-step-one-" + conference_id).show();
                    $("#saveIdentifier-" + conference_id).show();
                    window.open("/conferences/" + conference_id + "/conferenceConnection", "_blank");
                    window.location.reload();
                }
            });

            function mobile_Desktop_join(conference_id) {
                window.open("/conferences/" + conference_id + "/join_conference_mobile", "_blank");
                window.location.href = "/conferences/" + conference_id + "/conferenceConnection";
            }

            function h323_join(conference_id, type) {
                $("#" + type + "Modal-" + conference_id).modal("show");
            }

            $(document).on("click", '[id^=saveIdentifier]', function () {
                var conference_id = $(this).attr('id').split('-').pop(-1);
                var ip = $("#FieldH323IP-"+conference_id).val();

                $.post("/conferences/" + conference_id + "/inviteH323ToConference", {_token: '{{csrf_token()}}',H323IP:ip})
                    .done(function (data) {
                        if (data.status === 'success') {
                            $("#H323-step-one-"+conference_id).hide();
                            $("#saveIdentifier-"+conference_id).hide();
                            $("#H323-step-two-"+conference_id).show();
                            let timer_element = $('#timer_wrapper_'+conference_id);
                            let timer_options = {
                                onComplete: function (element) {
                                    $("#H323Modal-" + conference_id).modal("hide");
                                    window.location.reload();
                                }
                            };
                            timer_element.startTimer(timer_options);
                        }else{
                            $("#H323-step-one-errors-"+conference_id).html(data.error_message);
                        }
                    });
            });

            $(document).on("click", '.GotoTele', function () {

                var conference_id = $(this).attr('id').split('-').pop(-1);
                var device = $(this).attr("data-device");

                switch (device) {
                    case "Desktop-Mobile":
                        mobile_Desktop_join(conference_id);
                        break;
                    case "H323":
                        h323_join(conference_id, 'H323');
                        break;
                }
            });

            // Conference table functions

            $("[id^=openConferenceDetails]").on("click", function () {
                var conference = $(this).attr('id').split('-').pop(-1);
                var conference_details_ele = $("#conferenceDetails-" + conference);

                if (conference_details_ele.hasClass("out")) {
                    conference_details_ele.addClass("in");
                    conference_details_ele.removeClass("out");
                } else if (conference_details_ele.hasClass("in")) {
                    conference_details_ele.addClass("out");
                    conference_details_ele.removeClass("in");
                } else {
                    conference_details_ele.addClass("in");
                }
            });

            $(".pagination").addClass("pull-right");

            // Advanced search
            $("#searchInvisible, #searchInstitution, #searchDepartment").select2({
                containerCssClass: "select2-container-sm",
                dropdownCssClass: "tpx-select2-drop",
                allowClear: true
            });


            $("#searchInvisible").select2({allowClear: true, placeholder: "{!!trans('conferences.selectState')!!}"});

            $("#searchDepartment").select2({
                placeholder: "{!!trans('conferences.selectInstitutionFirst')!!}",
                allowClear: true
            });


            $("#searchInstitution").select2({
                allowClear: true, placeholder: "{!!trans('conferences.selectInstitution')!!}"
            }).on("change", function () {
                if ($("#searchInstitution").val() > 0) {
                    $("#searchDepartment").select2({allowClear: true}).load("/institutions/departments/" + $("#searchInstitution").val());
                } else if ($("#searchInstitution").val() == "") {
                    $("#searchDepartment").select2("data", null, {
                        placeholder: "{!!trans('conferences.selectInstitutionFirst')!!}",
                        allowClear: true
                    });
                }
            }).trigger("change");

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

            // Table limits
            $("#datatablesChangeDisplayLength").val({{ isset($_GET['limit']) ? $_GET['limit'] : 10 }});

            $("#datatablesChangeDisplayLength").change(function () {
                var value = $("select option:selected").val();
                var limit = value;
                if (value === "-1") {
                    var limit = <?php if (!empty($conferences)) {
                        echo $conferences->total();
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
            //Default class
            var sortings = ["id", "title", "start", "end", "invisible"];
            var url = window.location.href;
            $.each(sortings, function (index, value) {
                if (url.search("sort_" + value) > 0 && value != "start") {
                    $("#sort_" + value).removeClass("sorting");
                    $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
                    $("#sort_start").removeClass("sortingdesc");
                    $("#sort_start").addClass("sorting");
                } else if (url.search("sort_" + value) > 0 && value == "start") {
                    $("#sort_" + value).removeClass("sortingdesc");
                    $("#sort_" + value).addClass("sorting" + $.urlParam("sort_" + value));
                }
            });


            $("[id^=sort]").on("click", function () {
                var sortings = ["id", "title", "start", "end", "invisible"];
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
                    window.location.assign(url_pathname + "?" + current_param + $.param(params));
                }
            });

            $("#NewTele").click(function () {
                window.location.href = "/conferences/create";
            });

            $("#NewTestTele").click(function () {
                window.location.href = "/test-conferences/create";
            });


            $("[id^=RowBtnDelete]").on("click", function () {
                var row = $(this).closest('tr');
                var nRow = row[0];
                var conference = $(this).attr('id').split('-').pop(-1);
                var r = confirm("{!!trans('conferences.confirmDeleteConference')!!}");
                if (r == true) {
                    $.get("/conferences/delete/" + conference)
                        .done(function (data) {
                            obj = JSON.parse(data);
                            var oldvalue = obj.oldValue;
                            if (obj.status == 'error') {
                                alert("" + obj.data);
                            } else if (obj.status == 'success') {
                                alert("" + obj.data);
                                nRow.remove();
                                return false;
                            }
                        })
                        .fail(function (xhr, textStatus, errorThrown) {
                            alert(xhr.responseText);
                        });
                }
            });


// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL


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


        });
    </script>
@endsection
@section('extra-css')
    <style>
        body {
            text-align: left;
        }

        .tooltip-inner {
            width: 160px;
            white-space: pre-wrap;
        }

        .container {
            min-width: 400px !important;
        }

        .zero-width {
            display: none;
            width: 0px;
        }

        .box-padding {
            padding: 20px 30px;
        }

        .equalheightCol {
            margin-bottom: -99999px;
            padding-bottom: 99999px;
        }

        .equalheightRow {
            overflow: hidden;
        }

        .loginButtonRow {
            display: table;
            height: 100%;
            overflow: hidden;
        }

        .loginButtonCell {
            display: table-cell;
            vertical-align: middle;
            white-space: normal;
        }

        .textDots {
            white-space: nowrap;
            width: 11em;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        table#conferenceTable th {
            font-size: 12px;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
        }

        table#conferenceTable td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            -o-text-overflow: ellipsis !important;
        }

        .cellID {
            min-width: 40px !important;
        }

        .cellDesc {
            max-width: 200px !important;
        }

        .cellStartDate {
            width: 60px !important;
        }

        .cellStartTime {
            width: 50px !important;
        }

        .cellEndtDate {
            width: 60px !important;
        }

        .cellEndTime {
            width: 50px !important;
        }

        .cellAdmin {
            max-width: 120px !important;
        }

        .cellUHV {
            width: 40px !important;
        }

        .cellParticipants {
            min-width: 40px !important;
            max-width: 50px !important;
        }

        .cellInvisible {
            width: 50px !important;
        }

        .cellButton {
            padding: 3px !important;
            width: 110px !important;
            min-width: 120px !important;
            max-width: 120px !important;
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

        .conference_details {
            cursor: pointer;
        }

        .hiddenRow {

            padding: 0 !important;
        }

        .spanConferenceTitle {
            white-space: pre-line;
        }

        .spanModeratorInfo {
            white-space: pre-line;
        }

        .table td.hiddenRow {
            white-space: nowrap;
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activeConferences td, .futureConferences td {
            word-wrap: break-word;
            min-width: 20px;
            max-width: 120px;
        }

        .GotoTele {
            white-space: normal;
        }

        .timer{
            margin-top:10px;
            margin-bottom:10px;
        }

    </style>
@endsection

@section('conference-active')
    class = "active"
@endsection

@section('content')
    <section id="vroom">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <!-- Tab line -START -->
                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="/conferences">{{trans('conferences.conferences')}}</a></li>
                            @if($authenticated_user->hasRole('SuperAdmin'))
                                <li><a href="/conferences/settings">{{trans('conferences.settings')}}</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <!-- Tab line -END -->
                <div class="row">
                    <div class="small-gap"></div>
                    @if($errors->isEmpty() == false && ($errors->has('H323IP') || $errors->has('max_h323_allowed')))
                        <ul class="alert alert-danger" style="margin: 0 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }} @if($errors->has('H323IP')) {{trans('conferences.clickToEditConnection1')}} <a data-toggle="modal"
                                                                                                                                href="#H323Modal-{{ session('conference_id') }}"
                                                                                                                                id="ConnectionEdit">{!! trans('conferences.clickToEditConnection2') !!} @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if($errors->isEmpty() == false && $errors->has('soap_error'))
                        <ul class="alert alert-danger" style="margin: 0 15px 10px 15px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div class="col-md-12">
                        <div class="col-md-6"  style="text-align: justify !important;">
                            @php $agent = new Jenssegers\Agent\Agent; @endphp
                            @switch($agent->platform())
                                @case("Windows")
                                <p>{!! trans('conferences.important_info',["download_url"=>'https://zoom.us/client/latest/ZoomInstaller.exe']) !!}</p>
                                @break
                                @case("OS X")
                                <p>{!! trans('conferences.important_info',["download_url"=>'https://zoom.us/client/latest/Zoom.pkg']) !!}</p>
                                @break
                                @case("AndroidOS")
                                <p>{!! trans('conferences.important_info',["download_url"=>'market://details?id=us.zoom.videomeetings']) !!}</p>
                                @break
                                @default
                                <p>{!! trans('conferences.important_info',["download_url"=>'https://zoom.us/download'] ) !!}</p>
                                @break
                            @endswitch
                        </div>
                        <div class="col-md-6" style="text-align: justify !important;">
                            <p>{!!trans('conferences.faq_notice')!!}</p>
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-bottom:50px;">
                        <div class="col-md-6">
                            <div id="active_conferences_container">
                                @include('conferences.active_conferences_table')
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div id="future_conferences_container">
                                @include('conferences.future_conferences_table')
                            </div>
                        </div>
                    </div>

                </div>
                <!-- DATATABLES START -->
                @if(!empty($conferences))
                    @if (session('status'))
                        <div class="alert alert-success" style="margin: 0px 0px 10px 0px">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ session('status') }}
                        </div>
                    @endif
                    <div class="row"> <!-- Row with search field and add button - START -->
                        <div class="col-md-8 col-sm-12 col-xs-12">
                            <div class="pull-left" style="width:110px">
                                <div class="input-group">
                                   <span class="input-group-addon"><i
                                               class="glyphicon glyphicon-align-justify"></i></span>
                                    <select class="form-control" id="datatablesChangeDisplayLength">
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="30">30</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                        <option value="{{ $conferences->total() }}">All</option>
                                    </select>
                                </div>
                            </div>
                            <div class="pull-left">
                                <div class="input-group" style="width:200px">
                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
                                    @if(str_contains( Request::path(), 'date') && $conferences->count() > 0)
                                        <input type="text" class="form-control datepicker" style="width:200px"
                                               id="datatablesSearchDateField"
                                               value="{{ $conferences->first()->getDate($conferences->first()->start)}}"
                                               placeholder="{{trans('conferences.selectDate')}}">
                                    @elseif(str_contains( Request::path(), 'date') && $conferences->count() == 0)
                                        <input type="text" class="form-control datepicker" style="width:200px"
                                               id="datatablesSearchDateField"
                                               value="{{ class_basename(Request::path()) }}"
                                               placeholder="{{trans('conferences.selectDate')}}">
                                    @else
                                        <input type="text" class="form-control datepicker" style="width:200px"
                                               id="datatablesSearchDateField"
                                               placeholder="{{trans('conferences.selectDate')}}">
                                    @endif
                                </div>
                            </div>
                            <div class="pull-left">
                                <div class="input-group" style="width:50px">
                                    @if($authenticated_user->hasRole('SuperAdmin'))
                                        <a href="/conferences/all?limit=50&sort_start=asc">
                                            <button type="button"
                                                    class="btn btn-default">{{trans('conferences.showAll')}}</button>
                                        </a>
                                    @else
                                        <a href="/conferences/all">
                                            <button type="button"
                                                    class="btn btn-default">{{trans('conferences.showAll')}}</button>
                                        </a>
                                    @endif

                                </div>
                            </div>
                            <div class="pull-left">
                                <div class="input-group" style="width:200px">
                                    <a class="btn btn-primary" role="button" data-toggle="collapse"
                                       href="#collapseAdvancedDearch" aria-expanded="false"
                                       aria-controls="collapseAdvancedDearch"
                                       style="margin-left:5px;">{{trans('conferences.search')}} <span
                                                class="glyphicon glyphicon-search" aria-hidden="true"></span></a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 col-xs-12" style="text-align:right;">
                            <div class="row">
                                <div class="btn-group" role="group" aria-label="Actions">
                                    <button type="button" class="btn btn-success"
                                            style="padding-right:6px; padding-left:6px; margin-right:6px; margin-left:6px;" id="NewTele" data-toggle="tooltip"
                                            data-placement="top" title="{{trans('conferences.addConference')}}">
                                        <small><span
                                                    class="glyphicon glyphicon-plus-sign"></span> {{trans('conferences.conference')}}
                                        </small>
                                    </button>
                                    <button type="button" class="btn btn-primary"
                                            style="padding-right:6px; padding-left:6px; margin-right:6px; margin-left:6px;" id="NewTestTele" data-toggle="tooltip"
                                            data-placement="top" title="{{trans('conferences.addTestConference')}}">
                                        <small><span
                                                    class="glyphicon glyphicon-plus-sign"></span> {{trans('conferences.testConference')}}
                                        </small>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> <!-- Row with search field and add button - END -->

                    @include('conferences._advancedSearch', [])

                    <div class="table-responsive">
                        @include('conferences._conferenceTable', [])
                    </div>
            @endif
            <!-- DATATABLES END -->

            </div>
            <!--/.box-->
        </div>
        <!--/.container-->


        <!-- modal Already Connected to conference -->
        <div class="modal fade" id="alreadyConnected" tabindex="-1" role="dialog" aria-labelledby="H323ModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="UserLabel">{{trans('conferences.connectFailed')}}</h4>
                    </div>
                    <!-- .modal-header -->
                    <div class="modal-body">
                        {{trans('conferences.alreadyConnected')}}
                    </div>
                    <!-- .modal-body -->

                    <div class="modal-footer" style="margin-top:0;">
                        <button type="button" id="alreadyConnectedButtonClose"
                                class="btn btn-default">{{trans('conferences.close')}}</button>
                    </div>
                    <!-- .modal-footer -->
                </div>
                <!-- .modal-content -->
            </div>
            <!-- .modal-dialog -->
        </div>
        <!-- .modal -->
        <div class="modal fade" id="roomIsLocked" tabindex="-1" role="dialog" aria-labelledby="H323ModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="UserLabel">{{trans('conferences.connectionInfo')}}</h4>
                    </div>
                    <!-- .modal-header -->
                    <div class="modal-body">
                        {!!trans('conferences.roomLockedModerator')!!}
                    </div>
                    <!-- .modal-body -->

                    <div class="modal-footer" style="margin-top:0px;">
                        <button type="button" id="roomLockedButtonClose"
                                class="btn btn-default">{{trans('conferences.close')}}</button>
                    </div>
                    <!-- .modal-footer -->
                </div>
                <!-- .modal-content -->
            </div>
            <!-- .modal-dialog -->
        </div>
        <!-- modal Already Connected to conference end -->
    </section>
@endsection