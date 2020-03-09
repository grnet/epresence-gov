@if($has_active_notifications)
    <script type="text/javascript">
        $(document).ready(function () {
            $("#notifications_modal").modal('show');
            $("#notifications_modal_close").on('click', function () {
                if ($("#dont_show_again").prop("checked") === true) {
                    $("#setting_cookie").show();
                    $.post("/set_cookie", {
                        cookie_name: "dont_show_notification_" + {{$notification->id}},
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
    </script>
    <div class="modal fade" id="notifications_modal" tabindex="-1" role="dialog"
         aria-labelledby="notifications_modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="notification-title">{!! $notification->{session()->get('locale').'_title'}  !!}</h4>
                </div>
                <!-- .modal-header -->
                <div class="modal-body" id="notification-body">
                    <p>{!! $notification->{session()->get('locale').'_message'}  !!}</p>
                </div>
                <!-- .modal-body -->
                <div class="modal-footer" style="margin-top:0;">
                    <div class="row">
                        <div class="col-sm-6"><label style="float:left; font-weight:normal;"><input type="checkbox" id="dont_show_again" style="float:left;">&nbsp;{{trans('conferences.dontShowAgain')}}</label>
                        </div>
                        <div class="col-sm-6">
                            <div id="setting_cookie" style="float:right; margin-top:10px; display:none;">
                                <div class='uil-default-css' style='transform:scale(0.1); height: 100%;'>
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
                                </div>
                            </div>
                            <button type="button" id="notifications_modal_close" class="btn btn-default" style="float:right;"><span>OK</span></button>
                        </div>
                    </div>
                </div>
                <!-- .modal-footer -->
            </div>
            <!-- .modal-content -->
        </div>
        <!-- .modal-dialog -->
    </div>
@endif

