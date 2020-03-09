
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
    <script type="text/javascript" src="/js/bootstrap3-typeahead.js"></script>
    <script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
    <script type="text/javascript" src="/datatables/date-eu.js"></script>
    <link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP
            $(document).ready(function() {

                setTimeout(function() {
                    location.reload();
                }, 30000);

                $("[id^=openParticipantDetails]").on("click", function() {
                    var user = $(this).attr('id').split('-').pop(-1);
                    if($("#participantDetails-"+user).hasClass("out")) {
                        $("#participantDetails-"+user).addClass("in");
                        $("#participantDetails-"+user).removeClass("out");
                    } else if($("#participantDetails-"+user).hasClass("in")) {
                        $("#participantDetails-"+user).addClass("out");
                        $("#participantDetails-"+user).removeClass("in");
                    }else{
                        $("#participantDetails-"+user).addClass("in");
                    }
                });


                // Disconnect All
                $("#disconnectAll").click(function() {
                    var r = confirm("{!!trans('conferences.demoRoomDisconnectConfirmation')!!}");
                    if (r == true) {
                        $.post( "/demo_room/disconnectAll",
                            {
                                _token: "{{csrf_token()}}",
                            } )
                            .done(function(data) {
                                if (data.status === 'success'){
                                    alert("{!!trans('conferences.allUsersDisconnected')!!}");
                                }else{
                                    console.log(data.status);
                                    console.log(data.message);
                                }
                            })
                            .always(function() {
                                window.location = "/demo-room/manage";
                            });
                    }
                });

            });
// ΛΕΠΤΟΜΕΡΕΙΕΣ ΤΗΛΕΔΙΑΣΚΕΨΗΣ
    </script>
@endsection
@section('extra-css')
    <style>
        .container
        {
            min-width: 550px !important;
        }
        .zero-width {
            display:none;
            width: 0px;
        }

        table#participantsTable th {
            font-size:12px;
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
            font-size:12px;
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

        table#participantsTable thead .sorting, table thead .sortingasc, table thead .sortingdesc{
            cursor:pointer;
        }

        .participant_details{
            cursor:pointer;
        }
        .hiddenRow {
            padding: 0 !important;
        }

        .cellDetails {
            width: 20px !important;
        }
        .cellPName {
            width: 150px !important;
        }
        .cellPEmail {
            width: 100px !important;
        }
        .cellPStatus {
            width: 60px !important;
        }
        .cellPState {
            width: 20px !important;
        }
        .cellPDevice {
            width: 85px !important;
        }
        .cellPConnected {
            width: 85px !important;
        }
        .cellPButton {
            padding: 3px !important;
            width: 50px !important;
            min-width:50px !important;
            max-width:50px !important;
        }
        tfoot {
            display: table-header-group;
        }
    </style>
@endsection

@section('demo-active')
    class="active"
@endsection
@section('content')
    <section id="manageConference">
        <div class="container">

            <div class="box" style="padding:0px; background-color:transparent; margin-top:100px">
                <h3 id="TeleTile">{{trans('conferences.manageDemoroom')}}</h3>
            </div>
            <div class="box" style="padding:30px 30px  20px 30px" >
                <div class="row">
                    <div id="counter" class="col-md-12 col-sm-12 col-xs-12">
                        <h4>{{trans('conferences.connectedUsers')}}: {{$participant_data['total_users_in_progress']}}</h4>
                    </div>
                    <div class=" col-12" style="text-align:right;">
                            <button type="button" class="btn btn-danger" style="padding-right:6px; padding-left:6px" id="disconnectAll" data-toggle = "tooltip" data-placement = "top" title = "{{trans('conferences.terminateConferenceDisconnectUsers')}}">
                                <small><strong><span class="glyphicon glyphicon-off"></span></strong> {{trans('conferences.disconnectAll')}}</small>
                            </button>
                    </div>
                </div> <!-- Row with search field and add button - END -->
                <div class="small-gap"></div>
                <table style="margin-top:10px; width:100%;" class="table table-hover table-striped table-bordered">
                    <thead>
                    <tr>
                        <th class="cellDetails"></th>
                        <th class="cellDetails">ID</th>
                        <th class="cellPName hidden-xs sortingasc" id="sort_lastname">{{trans('conferences.fullName')}}</th>
                        <th class="cellPEmail">Email</th>
                        <th class="cellPAddress">{{trans('conferences.address')}}</th>
                        <th class="cellPDuration">Λειτουργικό</th>
                        <th class="cellPDuration">{{trans('conferences.join_time')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($participant_data['participants'] as $participant)
                        <tr>
                            <td class="cellDetails main_table" id="openParticipantDetails-{{ $participant->user->id }}"><span data-toggle="tooltip" data-placement="bottom" title="{{trans('conferences.details')}}" class="glyphicon glyphicon-zoom-in participant_details" aria-hidden="true"></span></td>
                            <td>{{ $participant->user->id }}</td>
                            <td class="cellPName hidden-xs">{{ $participant->user->lastname }} {{ $participant->user->firstname }}</td>
                            <td class="cellPEmail">{{ $participant->user->email }}</td>
                            <td>{{ $participant->address }}</td>
                            <td>{{ $participant->device }}</td>
                            <td>{{ $participant->join_time }}</td>
                        </tr>
                        <tr>
                            <td colspan="12" class="hiddenRow">
                                <div class="accordian-body collapse" id="participantDetails-{{ $participant->user->id }}">
                                    <table class="table">
                                        <tbody>
                                        <tr>
                                            <td>
                                                <strong>{{trans('conferences.fullName')}}:</strong> {{ $participant->user->lastname }} {{ $participant->user->firstname }}<br/>
                                                <strong>Email:</strong> {{ $participant->user->email }}<br/>
                                                @php
                                                $extra_emails_sso = $participant->user->extra_emails_sso()->toArray();
                                                $extra_emails_custom = $participant->user->extra_emails_custom()->toArray();
                                                @endphp
                                                @if((count($extra_emails_sso)+count($extra_emails_custom))>0)
                                                    <span style="font-weight:bold;">{{trans('users.extraEmail')}}:</span>
                                                    @foreach($extra_emails_sso as $mail)
                                                        <div style="color:green;">
                                                            {{$mail['email']}} (sso {{trans('users.emailConfirmedShort')}})
                                                        </div>
                                                    @endforeach
                                                    @if(count($extra_emails_custom)>0)
                                                    <div style="padding-bottom:7px;">
                                                        @foreach($extra_emails_custom as $mail)
                                                            @if($mail['confirmed'] == 0)
                                                                <div style="color:red;">
                                                                    {{$mail['email']}} ({{trans('users.customExtraMail')}}  {{trans('users.emailNotConfirmedShort')}})
                                                                </div>
                                                            @else
                                                                <div style="color:green;">
                                                                    {{$mail['email']}} ({{trans('users.customExtraMail')}}  {{trans('users.emailConfirmedShort')}})
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                @endif
                                                <strong>{{trans('users.confirmed')}}:</strong>
                                                @if($participant->user->confirmed == 0)
                                                    {{trans('users.no')}}<br/>
                                                @else
                                                    {{trans('users.yes')}}<br/>
                                                @endif
                                                <strong>{{trans('conferences.telephone')}}:</strong> {{ $participant->user->telephone }}<br/>
                                                <strong>{{trans('conferences.userType')}}:</strong> {{ trans($participant->user->roles->first()->label) }}<br/>
                                                @if($participant->user->institutions->count() > 0 && $participant->user->institutions->first()->slug == 'other')
                                                    <strong>{{trans('conferences.institution')}}:</strong> {{ $participant->user->institutions->first()->title }} ({{ ($participant->user->customValues()['institution']) }})<br/>
                                                    <strong>{{trans('conferences.department')}}:</strong> {{ $participant->user->departments->first()->title }} ({{ ($participant->user->customValues()['department']) }})
                                                @else
                                                    <strong>{{trans('conferences.institution')}}:</strong> {{ $participant->user->institutions->first()->title or trans('conferences.notDefinedYet') }}<br/>
                                                    <strong>{{trans('conferences.department')}}:</strong> {{ $participant->user->departments->first()->title or trans('conferences.notDefinedYet') }}<br/>
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="small-gap"></div>
                <!-- DATATABLES END -->

                <div class="row">
                    <div class="col-sm-12" id="TeleGroupButtons">
						<span class="pull-right">
							<a href="/demo-room"><button type="button" class="btn btn-default" id="TeleReturn" >{{trans('conferences.return')}}</button></a>
						</span>
                    </div>
                </div>

            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection
