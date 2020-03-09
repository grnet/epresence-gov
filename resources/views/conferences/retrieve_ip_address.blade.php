@extends('app')

@section('header-javascript')
    <!-- Font Awesome -->
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/font-awesome.css">
    <!-- Checkbox -->
    <script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">
    <link href="/select2/select2.css" rel="stylesheet">
    <link rel="stylesheet" href="/select2/select2-small.css">

    <!-- JS Countdown -->
    <script src="/js_countdown/jquery.simple.timer.js"></script>

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">

        $(document).ready(function () {

            var ip_exists = {{!empty($ip_address) ? 1 : 0}};

            var user_id = '{{Auth::user()->id}}';
            var channel = 'conference-user-' + user_id;

            var timer_options = {
                onComplete: function (element) {
                    if (ip_exists === 0)
                        window.location = "/conferences";
                    else {
                        $('.timer').hide();
                    }
                },
                onTwoMinTrigger: function (element) {
                }
            };

            $('.timer').startTimer(timer_options);

            @if(empty($ip_address))

            Echo.private(channel)
                .listen('.h323-address-retrieved', function (data) {
                    ip_exists = 1;
                    $("#h323_retrieval_container").hide();
                    $("#FieldH323IP").val(data.ip_address);
                    $("#h323_connection_container").show();
                });
            @endif

            $(document).on("click", '#saveIdentifier', function () {

                var ip = $("#FieldH323IP").val();

                $.post("/conferences/{{$conference->id}}/inviteH323ToConference", {
                    _token: '{{csrf_token()}}',
                    H323IP: ip
                })
                    .done(function (data) {
                        if (data.status === 'success') {
                            $("#H323-step-one").hide();
                            $("#saveIdentifier").hide();
                            $("#H323-step-two").show();
                            $("#H323ConnectionDetailsClose").show();
                        } else {
                            $("#H323-step-one-errors").html(data.error_message);
                        }
                    });
            });

            $(document).on("click", '#H323ConnectionDetailsClose', function () {
                window.location.assign("/conferences/{{$conference->id}}/conferenceConnection");
            });

        });
    </script>
@endsection
@section('extra-css')
    <style>
        .grouped-form {
            margin: 20px;
            overflow: auto;
        }

        .timer {
            margin-top: 10px;
            margin-bottom: 40px;
            color: #777;
            font-weight: 300;
        }

    </style>
@endsection
@section('content')
    <section>
        <div class="container">
            <div class="box first" style="margin-top:100px; padding:100px; overflow:auto;">
                @if(isset($seconds_left))
                    <div class="timer col-md-12" id="timer_wrapper" data-seconds-left={{$seconds_left}}><span>{{trans('conferences.timeRemainForTestConnection')}}: </span>
                    </div>
                @endif
                @if(session()->has('error'))
                    <div class="col-sm-12 alert alert-danger">
                        <span>{{session()->get('error')}}</span>
                    </div>
                @else
                    @if(empty($retrieved_ip_address))
                        <div id="h323_retrieval_container" class="col-sm-12">
                            <h4>{!! trans('conferences.ip_retrieval_intro',['ip_address'=>$test_connection_ip_address,'meeting_id'=>$test_connection_meeting_id]) !!}</h4>
                        </div>
                    @endif
                    <div id="h323_connection_container" style="@if(empty($retrieved_ip_address))display:none; @endif">
                        <div id="H323-step-one">
                            <div class="col-sm-12" style="margin-bottom:20px;">
                                <span>{!! trans('conferences.h323_first_step_ip_retrieval_text') !!}</span>
                                <div id="H323-step-one-errors" class="alert-danger"
                                     style="margin-top:5px; margin-bottom:5px;"></div>
                            </div>
                            <div class="form-group">
                                {!! Form::label('FieldH323IP', trans('conferences.Η323ipAddress').':', ['class' => 'control-label col-sm-4']) !!}
                                <div class="col-sm-8">
                                    {!! Form::text('H323IP', $retrieved_ip_address, ['class' => 'form-control', 'placeholder' => trans('conferences.enterIpUri'), 'id' => 'FieldH323IP']) !!}
                                    <div class="help-block with-errors" style="margin:0;"></div>
                                </div>
                            </div>
                        </div>
                        <div id="H323-step-two" style="display:none;">
                            <div class="col-sm-12" style="margin-bottom:20px;">
                                <span>{!! trans('conferences.h323_second_step_text',["ip_address"=>config('services.zoom.emea_ip_address'),"meeting_id"=>$conference->zoom_meeting_id]) !!}</span>
                            </div>
                            <div class="col-md-12">
                                <span class="form-control"><strong>{{$conference->getDialString()['h323']}}</strong></span>
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                            <div class="col-md-12">
                                <span class="form-control"><strong>{{$conference->getDialString()['sip']}}</strong></span>
                                <div class="help-block with-errors" style="margin:0;"></div>
                            </div>
                        </div>
                        <div class="col-md-12" style="text-align: right; margin-top:20px;">
                            <button class="btn btn-success m-right btn-border"
                                    id="saveIdentifier">{!! trans('deptinst.next') !!}</button>
                            <button type="button" id="H323ConnectionDetailsClose" style="display:none;"
                                    class="btn btn-default m-right btn-border">{{trans('conferences.close')}}</button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
