@extends('static')

@section('head-extra')
    <script src="bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
    <link rel="stylesheet" href="bootstrap-checkbox-x/checkbox-x.css">
    <script src="/clipboard/clipboard.min.js"></script>

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
    </style>
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <script type="text/javascript">
        $(document).ready(function () {

            $("#AcceptTermsAlert").hide();

            $("#loginButton").click(function () {
                if (!document.getElementById('AcceptTerms').checked) {
                    $("#AcceptTermsAlert").show()
                }
                else {

                    window.open('/join_demo_room', '_blank');
                    // $("#AcceptTermsAlert").hide();
                    // $("#mobileLinuxMessage-demoRoom").modal("show");
                    // var clipboard = new Clipboard('.copyEmail');
                }
            });


            $("#mobileLinuxMessageButtonClose-demoRoom").click(function () {
                $("#mobileLinuxMessage-demoRoom").modal("hide");
            });

            $("#mobileLinuxConnectID-demoRoom").click(function () {

                var win = window.open('{!!env('VIDYO_DEMO_ROOM_URL')!!}', '_blank');
                win.focus();
                $("#mobileLinuxMessage-demoRoom").modal("hide");
            });

            // ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection

@section('demo-active')
    class="active"
@endsection

@section('content')

    <section id="Demo">
        <div class="container">
            <div class="box box-padding" style="margin-top:100px; ">
                <div class="row">
                    <div class="col-md-2 col-sm-3 hidden-xs"><img src="images/DemoRoom.png" class="img-responsive"
                                                                  style="margin-top:20px"></div>
                    <div class="col-md-10 col-sm-9 ">
                        <h4 style="color:#52B6EC">{!!trans('site.demoRoomDescr')!!}</h4>
                    </div>
                </div>
            </div>

            <div class="medium-gap"></div>

            <div class="row">
                <div class="equalheightRow">

                    <div class="col-md-6">
                        <div class="box box-padding equalheightCol">
                            <h4 style="color:#52B6EC">{{trans('site.testConnection')}}</h4>
                            <hr>
                            <div class="col-md-12 alert alert-danger" style="margin-bottom:25px;">
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
                            <div class="col-md-12 alert alert-danger" style="margin-bottom:25px;">
                                <p>{!!trans('conferences.faq_notice')!!}</p>
                            </div>
                            <button id="loginButton" type="button" class="btn  btn-lg btn-success" style="width:100%; line-height:50px;">
                                    <span style="vertical-align: middle; font-size:30px">
                                        <i class="fa fa-sign-in"></i>
                                    </span>
                                <span style="vertical-align: middle;">
                                        {{trans('site.connectDemo')}}
                                    </span>
                            </button>
                            <div id="AcceptTermsAlert" class="alert alert-warning" role="alert" style="margin-top:20px">
                                <span class="glyphicon glyphicon glyphicon-warning-sign"></span> {{trans('site.mustAcceptTerms')}}
                            </div>
                            <div style="margin-top:20px">
                                <label class="cbx-label" for="AcceptTerms"><input type="checkbox" id="AcceptTerms"
                                                                                  style="margin-left:10px; margin-right:10px; padding-bottom:5px;">{{trans('site.acceptTerms')}}
                                </label>
                            </div>
                            <div class="small-gap"></div>
                        </div>
                    </div>
                    <div class="col-md-6 ">
                        @if ( Auth::user()->hasRole('SuperAdmin'))
                            <div class="box box-padding equalheightCol">
                                <a href="/demo-room/manage"><button class="btn btn-primary">{{trans('conferences.manage')}}</button></a>
                            </div>
                        @endif
                        <div class="box box-padding equalheightCol">
                            <h5>{{trans('terms.demoRoomTermsTitle')}}</h5>
                            <small><p>{{trans('terms.demoRoomTerms')}} <a href="/contact">"{{trans('site.contact')}}"</a>.</p></small>
                            <div class="small-gap"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.container-->
    </section>

    <div class="modal fade" id="mobileLinuxMessage-demoRoom" tabindex="-1" role="dialog"
         aria-labelledby="H323ModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"
                            aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="UserLabel">{{trans('conferences.connectionInfo')}}</h4>
                </div>
                <!-- .modal-header -->
                <div class="modal-body">
                    @if(!$is_mobile)
                        <ol>
                            {!!trans('conferences.confirmMobileCap1')!!}
                            <span id="copyEmail">{{Auth::user()->email}}</span>
                            <button class="copyEmail btn btn-default btn-sm" data-clipboard-target="#copyEmail"><i class="fa fa-files-o"></i>
                            </button> {!!trans('conferences.confirmMobileCap2')!!}
                            <li>{!!trans('conferences.confirmMobileCap3')!!}{{env('VIDYO_DEMO_ROOM_PIN')}}</li>
                        </ol>
                    @else
                        <ol>
                            <li>{!!trans('conferences.confirmDesktopCap1')!!}
                                <span id="copyEmail">{{Auth::user()->email}}</span>
                                <button class="copyEmail btn btn-default btn-sm" data-clipboard-target="#copyEmail"><i class="fa fa-files-o"></i>
                                </button>
                            </li>
                            {!!trans('conferences.confirmDesktopCap')!!}
                            <li>{!!trans('conferences.confirmDesktopCap2')!!}{{env('VIDYO_DEMO_ROOM_PIN')}}</li>
                        </ol>
                    @endif
                </div>
                <!-- .modal-body -->
                <div class="modal-footer" style="margin-top:0;">
                    <button type="button" id="mobileLinuxMessageButtonClose-demoRoom" class="btn btn-default">{{trans('conferences.cancel')}}</button>
                    <button type="button" id="mobileLinuxConnectID-demoRoom" class="btn btn-default">{{trans('conferences.continue')}}</button>
                </div>
                <!-- .modal-footer -->
            </div>
            <!-- .modal-content -->
        </div>
        <!-- .modal-dialog -->
    </div>
@endsection