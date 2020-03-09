<script type="text/javascript">
    $(document).ready(function () {
        var isMac = navigator.platform.toUpperCase().indexOf('MAC');

        var active_notifications = {!! App\Notification::where('enabled',1)->count() !!};			//NOTIFICATIONS START
        var locale = '{!! Session::get('locale') !!}';


        @if(App\Notification::where('enabled',1)->count() >0)

        if (active_notifications === 1) {
            var notification = JSON.parse('{!! App\Notification::where('enabled',1)->first()!!}');
            var show_on_user = {{ count(array_intersect(Auth::user()->roles()->pluck('id')->toArray(),explode(",",App\Notification::where('enabled',1)->first()->role_ids))) }};
            var group = "{{App\Notification::where('enabled',1)->first()->type}}";
                    @if(Cookie::get('dont_show_'.App\Notification::where('enabled',1)->first()->id))
            var dont_show_notification = {{Cookie::get('dont_show_'.App\Notification::where('enabled',1)->first()->id)}};
                    @else
            var dont_show_notification = null;

            @if(in_array(Auth::user()->getUserOS(),array('iPad','iPhone','Android','Linux')))
                show_on_user = 0;
            @endif
                    @endif
            if (show_on_user > 0 && (group === "global" || group === "client") && !dont_show_notification) {
                if (locale === 'el') {
                    @if(Auth::user()->ChromeOrNot())

                    //GREEK CHROME MESSAGE

                    $("#notification-title").html(notification.el_title);

                    if (isMac >= 0) {
                        $("#notification-body").html(notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoClientForWeb-macosx-x64-1.3.14.0001.pkg"> εδώ</a>.</p>');
                    }
                    else {
                        $("#notification-body").html(notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoClientForWeb-win32-1.3.14.0001.msi"> εδώ</a>.</p>');
                    }


                    @else
                    //GREEK NON CHROME MESSAGE
                    $("#notification-title").html(notification.el_title);
                    if (isMac >= 0) {
                        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
                            $("#notification-body").html('<ul><li>{!! trans('conferences.firefoxMacUsage') !!}</li><li>' + notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg"> εδώ</a>.</p></li>');
                        }
                        else {
                            $("#notification-body").html(notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg"> εδώ</a>.</p>');

                        }

                    }
                    else {
                        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
                            $("#notification-body").html('<ul><li>{!! trans('conferences.firefoxWindowsUsage') !!}</li><li>' + notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi"> εδώ</a>.</p></li>');
                        }
                        else {
                            $("#notification-body").html(notification.el_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi"> εδώ</a>.</p>');

                        }
                    }
                    @endif

                }
                else if (locale === 'en') {
                    @if(Auth::user()->ChromeOrNot())

                    //ENGLISH CHROME MESSAGE

                    $("#notification-title").html(notification.en_title);

                    if (isMac >= 0) {
                        $("#notification-body").html(notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoClientForWeb-macosx-x64-1.3.14.0001.pkg"> here</a>.</p>');
                    }
                    else {
                        $("#notification-body").html(notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoClientForWeb-win32-1.3.14.0001.msi"> here</a>.</p>');
                    }
                    @else
                    //ENGLISH NON CHROME MESSAGE
                    $("#notification-title").html(notification.en_title);
                    if (isMac >= 0) {
                        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {
                            $("#notification-body").html('<ul><li>{!! trans('conferences.firefoxWindowsUsage') !!}</li><li>' + notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg"> here</a>.</p></li><ul>');
                        }
                        else {
                            $("#notification-body").html(notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg"> here</a>.</p>');
                        }
                    }
                    else {
                        if (navigator.userAgent.toLowerCase().indexOf('firefox') > -1) {

                            $("#notification-body").html('<ul><li>{!! trans('conferences.firefoxMacUsage') !!}</li><li>' + notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi"> here</a>.</p></li></ul>');
                        }
                        else {
                            $("#notification-body").html(notification.en_message + '<a href="/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi"> here</a>.</p>');

                        }
                    }
                    @endif

                }
                $("#notifications_modal").modal('show');
            }
        }

        $("#notifications_modal_close").on('click', function () {
            if ($("#dont_show_again").prop("checked") == true) {
                $("#setting_cookie").show();
                $.post("/set_cookie", {
                    cookie_name: "dont_show_" + notification.id,
                    cookie_value: "1"
                }, function (result) {
                    console.log(result);
                }).done(function () {
                    $("#notifications_modal").modal('hide');
                });


            } else
                $("#notifications_modal").modal('hide');
        });
    });

@endif
</script>
<div class="modal fade" id="notifications_modal" tabindex="-1" role="dialog" aria-labelledby="notifications_modalLabel"      aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="notification-title">Notification Title</h4>
            </div>
            <!-- .modal-header -->
            <div class="modal-body" id="notification-body">
                <p>Notification message</p>
            </div>
            <!-- .modal-body -->

            <div class="modal-footer" style="margin-top:0;">
                <div class="row">
                    <div class="col-sm-4"><input type="checkbox" id="dont_show_again" style="float:left;"><span
                                style="float:left; margin-left:10px;">{{trans('conferences.dontShowAgain')}}</span>
                    </div>
                    <div class="col-sm-4"></div>
                    <div class="col-sm-4"><span id="setting_cookie"
                                                style="float:right; margin-top:10px; display:none;"><div
                                    class='uil-default-css' style='transform:scale(0.1); height: 100%;'>
                                    <div style='top:80px;left:93px;width:14px;height:14px;background:#00b2ff;-webkit-transform:rotate(0deg) translate(0,-60px);transform:rotate(0deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(30deg) translate(0,-60px);transform:rotate(30deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(60deg) translate(0,-60px);transform:rotate(60deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(90deg) translate(0,-60px);transform:rotate(90deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(120deg) translate(0,-60px);transform:rotate(120deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(150deg) translate(0,-60px);transform:rotate(150deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(180deg) translate(0,-60px);transform:rotate(180deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(210deg) translate(0,-60px);transform:rotate(210deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(240deg) translate(0,-60px);transform:rotate(240deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(270deg) translate(0,-60px);transform:rotate(270deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(300deg) translate(0,-60px);transform:rotate(300deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                    <div style='top:80px;left:93px;width:14px;height:40px;background:#00b2ff;-webkit-transform:rotate(330deg) translate(0,-60px);transform:rotate(330deg) translate(0,-60px);border-radius:10px;position:absolute;'></div>
                                </div></span>
                        <button type="button" id="notifications_modal_close" class="btn btn-default"
                                style="float:right;"><span>OK</span></button>
                    </div>
                </div>
            </div>
            <!-- .modal-footer -->
        </div>
        <!-- .modal-content -->
    </div>
    <!-- .modal-dialog -->
</div>