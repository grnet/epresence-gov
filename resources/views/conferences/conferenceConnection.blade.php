@extends('app')

@section('header-javascript')
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

    <link rel="stylesheet" href="/css/font-awesome.css">

    <!-- JS Countdown -->
    <script src="/js_countdown/jquery.simple.timer.js"></script>

    <link href="/css/main.css" rel="stylesheet">
    <link href="/css/eDatatables.css" rel="stylesheet">

    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">


    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">
        $(document).ready(function () {

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

            $('[data-toggle="tooltip"]').tooltip();

// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL


            var timer_options = {
                onComplete: function (element) {
                    window.location = "/conferences";
                },
                onTwoMinTrigger: function (element) {
                    $("#timer_wrapper").css({"color": "red", "text-decoration": "underline"});
                    var sound = $("#two_minutes_left")[0];
                    sound.play();
                }
            };

            $('.timer').startTimer(timer_options);

            //Play sound and color red on 2 minutes left

            var user_id = '{{Auth::user()->id}}';
            var channel = 'conference-user-' + user_id;

            Echo.private(channel)
                .listen('.participant-joined-conference', function (data) {

                    console.log("participant-joined-conference:");
                    console.log(data);

                    if (data.user_id === parseInt(user_id)) {
                        $("#logged-in-participant-online").show();
                        $("#logged-in-participant-offline").hide();
                    }
                    $("#participantConnected-" + data.user_id).addClass("active");
                });

            Echo.private(channel)
                .listen('.participant-left-conference', function (data) {
                    console.log("participant-left-conference:");
                    console.log(data);

                    if (data.user_id === parseInt(user_id)) {
                        $("#logged-in-participant-online").hide();
                        $("#logged-in-participant-offline").show();
                    }
                    $("#participantConnected-" + data.user_id).removeClass("active");
                });

            Echo.private(channel)
                .listen('.conference-details-changed', function (data) {

                    //Update ui when a future conference is activated
                    var fields_updated = data.fields_updated;

                    for (var property in fields_updated) {
                        if (fields_updated.hasOwnProperty(property)) {
                            switch (property) {
                                case "end":
                                    location.reload();
                                    break;

                                case "title":

                                    break;

                            }
                        }
                    }

                });
        });
    </script>
@endsection
@section('extra-css')
    <style>

        .container {
            min-width: 400px !important;
        }

        .noshadow {
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            border: 0px;
        }

        .template {
            padding: 40px 15px;
            text-align: center;
        }

        .actions {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .actions .btn {
            margin-right: 10px;
        }

        ul {
            list-style-type: none;
        }

        li.active {
            color: green;
        }
        .timer{
            margin-top:10px;
            margin-bottom:10px;
        }

    </style>
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <div class="row">
                    <div class="col-md-12">
                        <div class="template">
                            <img alt="ePresence-logo" src="/images/epresence-logo.png">
                            <div class="details">
                                <p id="logged-in-participant-offline"
                                   @if($conference->participantConferenceStatus(Auth::user()->id) == 1) style="display:none;" @endif>{{trans('conferences.uNotConnected')}}</p>
                                <p id="logged-in-participant-online"
                                   @if($conference->participantConferenceStatus(Auth::user()->id) == 0) style="display:none;" @endif>{{trans('conferences.uConnected')}}</p>
                                <div class="timer" id="timer_wrapper" data-seconds-left="{{$seconds_left+5}}"><span>{{trans('conferences.timeRemain')}}: </span></div>
                                <!--<span>{{trans('conferences.hourMins')}}</span> -->
                                <p>{{trans('conferences.liveParticipantList')}}:</p>
                                    <ul>
                                        @foreach($conference->participants as $participant)
                                            @if($conference->participantConferenceStatus($participant->id) == 1)
                                                <li class="active"
                                                    id="participantConnected-{{ $participant->id }}">{{ $participant->email }}
                                                    ({{trans('users.localUserShort')}}
                                                    : {{$participant->state_string($participant->state)}})
                                                </li>
                                            @elseif($conference->participantConferenceStatus($participant->id) == 0)
                                                <li id="participantConnected-{{ $participant->id }}">{{ $participant->email }}
                                                    ({{trans('users.localUserShort')}}: {{$participant->state_string($participant->state)}})
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                <a href="/conferences"><button type="button" class="btn btn-primary btn-sm m-right btn-border"> {!! trans('conferences.return') !!}</button></a>
                            </div>
                        </div>
                    </div>
                </div><!--/.box-->
            </div><!--/.container-->
        </div>
    </section>
    <audio id="two_minutes_left" preload="auto">
        <source src="/vidyo_client_resources/sounds/two_minutes_left.mp3"/>
    </audio>
@endsection

