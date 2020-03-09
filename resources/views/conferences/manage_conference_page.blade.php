@if(is_null(Session::get('previous_url')))
    {{ Session::put('previous_url', URL::previous()) }}
@endif
@extends('app')
@section('header-javascript')
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

    <link rel="stylesheet" href="/css/font-awesome.css">
    <link href="/css/main.css" rel="stylesheet">

    <script src="/clipboard/clipboard.min.js"></script>

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">

        var conference_id = '{{$conference->id}}';
        var channel = 'manage-conference-' + conference_id;
        var token = '{{csrf_token()}}';

        $(document).ready(function () {

            @if(Auth::user()->hasRole('SuperAdmin') || $conference->host_url_accessible)
               var clipboard = new Clipboard('.copyHostUrl');
                 clipboard.on('success', function(e) {
                    $(".copyHostUrl").css("color","black");
                     setTimeout(function(){  $(".copyHostUrl").css("color","white") }, 500);
                 });
            @endif

            //Participant changed status events

            Echo.private(channel)
                .listen('.participant-status-changed', function (data) {

                    var user_id = data.user_id;

                    if (data.status === "1") {
                        flip_participant_status(user_id, 1)
                    } else if (data.status === "0") {
                        flip_participant_status(user_id, 0)
                    }
                });


            Echo.private(channel)
                .listen('.participant-device-changed', function (data) {

                    var device_string = $("#device-" + data.user_id);
                    device_string.html(data.device);
                });


            var its_me_ending_the_conference = false;


            Echo.private(channel)
                .listen('.conference-ended', function (data) {
                    if (data.reason === "deleted") {
                        window.location.href = "/conferences";
                    } else {
                        if(!its_me_ending_the_conference){
                            setTimeout(function(){
                                window.location = "/conferences/" + data.conference_id + "/details";
                            }, 20000);
                        }
                    }
                });


            Echo.private(channel)
                .listen('.conference-lock-status-changed', function (data) {

                    flip_lock_status(data.status);
                });


            Echo.private(channel)
                .listen('.participant-added', function (data) {

                    var ele = $("#invited_participants");
                    var current_participants = parseInt(ele.html());

                    current_participants++;
                    ele.html(current_participants);

                    reload_participants_table();
                });


            Echo.private(channel)
                .listen('.participant-removed', function (data) {

                    var ele = $("#invited_participants");
                    var current_participants = parseInt(ele.html());

                    current_participants--;
                    ele.html(current_participants);

                    reload_participants_table();
                });


            Echo.private(channel)
                .listen('.conference-details-changed', function (data) {

                    var fields_updated = data.fields_updated;

                    for (var property in fields_updated) {
                        if (fields_updated.hasOwnProperty(property)) {
                            switch (property) {
                                case "title":
                                    $("#conferenceTitle").html(fields_updated[property]);
                                    break;
                            }
                        }
                    }
                });

            Echo.private(channel)
                .listen('.participant-joined-conference', function (data) {
                    $("#participantNotConnected-" + data.user_id).hide();
                    $("#participantConnected-" + data.user_id).show();

                    var ele = $("#online_participants");
                    var current_online_participants = parseInt(ele.html());

                    current_online_participants++;
                    ele.html(current_online_participants);

                });

            Echo.private(channel)
                .listen('.participant-left-conference', function (data) {
                    $("#participantConnected-" + data.user_id).hide();
                    $("#participantNotConnected-" + data.user_id).show();

                    var ele = $("#online_participants");
                    var current_online_participants = parseInt(ele.html());

                    current_online_participants--;
                    ele.html(current_online_participants);

                });


            // Disconnect All
            $("#disconnectAll").click(function () {
                var r = confirm("{!!trans('conferences.confirmConferenceEnd')!!}");
                if (r === true) {
                    its_me_ending_the_conference = true;
                    $.post("/conferences/" + conference_id + "/disconnectConferenceAllParticipants", {_token: token})
                        .done(function (data) {
                            obj = JSON.parse(data);
                            if (obj.status === 'success') {
                                alert("{!!trans('conferences.allUsersDisconnected')!!}");
                                window.location = "/conferences/";
                                // setTimeout(function(){
                                //    window.location = "/conferences/" + conference_id + "/details";
                                // }, 20000);
                            }
                        });
                }
            });

            $("body").on("click", "[id^=ParticipantStatusButton]", function () {

                var user_id = parseInt($(this).attr('id').split('-').pop(-1));
                var action = parseInt($(this).attr('data-action'));

                //action == 0 means that button will try to deactivate the participant
                //action == 1 means that button will try to activate the participant

                var r = confirm("{!!trans('conferences.confirmUserStateChange')!!}");
                if (r === true) {
                    $.post("/conferences/" + conference_id + "/changeParticipantStatus", {
                        _token: token,
                        user_id: user_id,
                        action: action
                    })
                        .done(function (data) {

                            if (data.status === "success") {
                                flip_participant_status(user_id, action);
                            }
                        }, "json")
                        .fail(function (xhr, textStatus, errorThrown) {
                            console.log(xhr.responseText);
                        });
                }
            });

            $("[id^=openParticipantDetails]").on("click", function () {

                var user_id = $(this).attr('id').split('-').pop(-1);

                var participant_details_row = $("#participantDetails-" + user_id);

                if (participant_details_row.hasClass("out")) {
                    participant_details_row.addClass("in");
                    participant_details_row.removeClass("out");
                } else if (participant_details_row.hasClass("in")) {
                    participant_details_row.addClass("out");
                    participant_details_row.removeClass("in");
                } else {
                    participant_details_row.addClass("in");
                }
            });

            // Lock or Unlock conference

            $("#lockUnlockRoom").click(function () {

                var action = $(this).attr('data-action');
                $.post("/conferences/" + conference_id + "/lockUnlockRoom", {_token: token, action: action})
                    .done(function (data) {
                        if (data.status === "success") {
                            var status = action === "lock" ? "locked" : "unlocked";
                            flip_lock_status(status);
                            data.participant_ids.forEach(function(user_id) {
                                if(status === "locked"){
                                    flip_participant_status(user_id,0);
                                }else{
                                    flip_participant_status(user_id,1);
                                }
                            });
                        }
                    })
                    .fail(function (xhr, textStatus, errorThrown) {
                        console.log(xhr.responseText);
                    });
            });
        });

        function flip_lock_status(status) {
            var unlock_room_string = '{{trans('conferences.unlockRoom')}}';
            var lock_room_string = '{{trans('conferences.lockRoom')}}';
            var lock_unlock_room = $("#lockUnlockRoom");
            lock_unlock_room.removeClass("btn-danger btn-success");
            lock_unlock_room.find("i").removeClass("fa-lock fa-unlock");
            if (status === "locked") {
                //Conference is locked update ui
                lock_unlock_room.attr("data-action", "unlock");
                lock_unlock_room.attr("title", unlock_room_string);
                lock_unlock_room.addClass("btn-success");
                lock_unlock_room.find("i").addClass("fa-unlock");
            } else {
                //Conference is unlocked update ui
                lock_unlock_room.attr("data-action", "lock");
                lock_unlock_room.attr("title", lock_room_string);
                lock_unlock_room.addClass("btn-danger");
                lock_unlock_room.find("i").addClass("fa-lock");
            }
        }

        function reload_participants_table() {

            $.get("/conferences/" + conference_id + "/manage/get_participants_table_container_ajax", function () {
            })
                .done(function (data) {
                    $("#participants_table_container").html(data);
                });
        }


        function flip_participant_status(user_id, status) {

            var active_string = '{{ trans('application.active') }}';
            var innactive_string = '{{ trans('application.inactive') }}';

            var participant_status_button = $("#ParticipantStatusButton-" + user_id);
            var updated_button_action_attribute = null;

            participant_status_button.removeClass();
            participant_status_button.addClass("btn btn-sm");
            participant_status_button.find(".icon_class").removeClass("glyphicon-ban-circle glyphicon-ok");


            if (status === 0) {

                participant_status_button.addClass("btn-danger");
                participant_status_button.find(".icon_class").addClass("glyphicon-ban-circle");
                updated_button_action_attribute = 1;

                participant_status_button.find(".message_container").html(innactive_string);
            }
            else {

                participant_status_button.addClass("btn-success");
                participant_status_button.find(".icon_class").addClass("glyphicon-ok");
                updated_button_action_attribute = 0;

                participant_status_button.find(".message_container").html(active_string);
            }

            participant_status_button.attr('data-action', updated_button_action_attribute);
        }


    </script>
@endsection
@section('extra-css')
    <style>
        .container {
            min-width: 550px !important;
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

        tfoot {
            display: table-header-group;
        }


        .action_buttons{
            margin-left:5px;
            margin-right:5px;
        }

        .table_icon{
            margin: 0 auto;
        }

        .cellPMuteStatus{
            text-align: center;
        }

        .groupButton{
            padding-right:6px;
            padding-left:6px;
        }

    </style>
@endsection
@section('conference-active')
    class = "active"
@endsection
@section('content')
    <section id="manageConference">
        <div class="container">
            <div class="box" style="padding:0; background-color:transparent; margin-top:100px">
                <h3 id="TeleTile">{{trans('conferences.manageConference')}}:</h3>
                <h4 style="word-wrap: break-word;" id="conferenceTitle">{{ $conference->title }}</h4>
            </div>
            <div class="box" style="padding:30px 30px  20px 30px">
                <div class="row">
                    <div id="counter" class="col-md-12 col-sm-12 col-xs-12">
                        <h4>{{trans('conferences.connectedUsers')}}: <span id="online_participants">{!! $conference->participants()->where('in_meeting', 1)->count() !!}</span>/<span id="invited_participants">{{ $conference->participants()->count() }}</span></h4>
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12" style="margin:20px;">
                        <p>{{trans('conferences.manage_conference_help')}}</p>
                        @if(Auth::user()->hasRole('SuperAdmin') || $conference->host_url_accessible)
                            <p>{{trans('conferences.manage_conference_join_as_host_help')}}</p>
                        @endif
                    </div>
                    <div class="col-md-12 col-sm-12 col-xs-12" style="text-align:right;">
                        <div class="row">
                            <div class="btn-group" role="group" aria-label="Actions">
                                    @if($conference->locked)
                                        <button type="button" class="btn btn-success action_buttons groupButton" id="lockUnlockRoom"
                                                style="padding-right:6px; padding-left:6px" data-action="unlock"
                                                data-toggle="tooltip" data-placement="top"
                                                title="{{trans('conferences.unlockRoom')}}">
                                            <strong><i class="fa fa-unlock" aria-hidden="true"></i></strong>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-danger action_buttons groupButton" id="lockUnlockRoom"
                                                style="padding-right:6px; padding-left:6px" data-action="lock"
                                                data-toggle="tooltip" data-placement="top"
                                                title="{{trans('conferences.lockRoom')}}">
                                            <strong><i class="fa fa-lock" aria-hidden="true"></i></strong>
                                        </button>
                                    @endif
                                <button type="button" class="btn btn-danger action_buttons"
                                        style="padding-right:6px; padding-left:6px" id="disconnectAll" data-toggle="tooltip" data-placement="top"
                                        title="{{trans('conferences.terminateConferenceDisconnectUsers')}}">
                                    <small><strong><span
                                                    class="glyphicon glyphicon-off"></span></strong> {{trans('conferences.terminateConference')}}
                                    </small>
                                </button>
                                @if(Auth::user()->hasRole('SuperAdmin') || $conference->host_url_accessible)
                                <button type="button" data-clipboard-text="{{url('/conferences/'.$conference->id.'/join_as_host')}}" class="btn btn-success copyHostUrl groupButton"
                                         data-toggle="tooltip" data-placement="top"
                                        title="{{trans('conferences.copy_host_link')}}">{{trans('conferences.copy_host_link')}}
                                </button>

                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- Row with search field and add button - END -->
                <div class="small-gap"></div>
                <div id="participants_table_container">
                    @include('conferences.manage_participants_table', ['sort' => Input::get()])
                </div>
                <div class="small-gap"></div>
                <div class="row">
                    <div class="col-sm-12" id="TeleGroupButtons">
						<span class="pull-right">      
							<a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default" id="TeleReturn">{{trans('conferences.return')}}</button></a>
						</span>
                    </div>
                </div>

            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection
