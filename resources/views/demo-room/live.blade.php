<!DOCTYPE html>
<html lang="en">
<title>Demo Room</title>
<meta charset="utf-8">
<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/start/jquery-ui.css">
<link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/mmedphfiemffkinodeemalghecnicmnh">
<link href="/bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
<link rel="stylesheet" href="/vidyo_client_resources/css/vidyoweb.css" media="screen">
<link rel="stylesheet" href="/vidyo_client_resources/css/demo_room.css" media="screen">
<script src="/vidyo_client_resources/scripts/myfunctions.js"></script>
<script src="/vidyo_client_resources/lib/jquery-1.12.2.min.js"></script>
<script src="/vidyo_client_resources/lib/jquery-ui.min.js"></script>
<script src="/vidyo_client_resources/scripts/vidyo/soap-proxy.js"></script>
<script src="/vidyo_client_resources/scripts/main.js"></script>
<script src="/vidyo_client_resources/scripts/front_scripts_demo.js"></script>
<script>
    var guestname = '<?php echo Auth::user()->email?>';
    var room_url = '<?php echo env('VIDYO_DEMO_ROOM_URL'); ?>';
    var locale = '{!! Session::get('locale') !!}';
    var roompin = '{!! env('VIDYO_DEMO_ROOM_PIN') !!}';
    var config = {
        'guestname': guestname,
        'roomurl': room_url,
        'roompin': roompin
    }
    $(document).ready(function () {
        $("#presentationMode").on("change", function () {
            var id = $('#presentationMode').prop('checked');
            id = id.toString();
            if (id == "true") {
                setPreferredMode(1);
                $("#presentationModetext").html(' {!!trans('conferences.presentationmodeEnabled')!!}');
            }
            else {
                setPreferredMode(0);
                $("#presentationModetext").html(' {!!trans('conferences.presentationmodeDisabled')!!}');
            }
        });
        var os = getOperatingSystem();
        if (os === 'iOS' || os === 'Android' || os === 'Linux') {
            window.location = room_url;
        }
        else if (os === 'Windows' || os === 'Mac') {

        }
        else {
            console.log('Your os is not supported');
            window.location = 'account';
        }

        var isMac = navigator.platform.toUpperCase().indexOf('MAC');
        if (isMac >= 0) {

            var html ='';
            if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                html = "<ol><li>{!!trans('conferences.firefoxMacUsage')!!}</li><li>{!!trans('conferences.downloadPluginForMac')!!} <a href='/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmFirefox1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>";
            }
            else {
                html = "<ol><li>{!!trans('conferences.downloadPluginForMac')!!} <a href='/vidyo_client_resources/installers/VidyoWeb-macosx-x64-1.3.14.0002.pkg'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmFirefox1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>";
            }

            $("#os_plugin_firefox").html(html);

            $("#os_plugin_chrome").html("<ol><li>{!!trans('conferences.downloadPluginForMac')!!} <a href='/vidyo_client_resources/installers/VidyoClientForWeb-macosx-x64-1.3.14.0001.pkg'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmChrome1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>");
        }
        else {

            var html2 ='';
            if(navigator.userAgent.toLowerCase().indexOf('firefox') > -1){
                html2 = "<ol><li>{!!trans('conferences.firefoxWindowsUsage')!!}</li><li>{!!trans('conferences.downloadPluginForWindows')!!} <a href='/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmFirefox1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>";
            }
            else{
                html2 = "<ol><li>{!!trans('conferences.downloadPluginForWindows')!!} <a href='/vidyo_client_resources/installers/VidyoWeb-win32-1.3.14.0002.msi'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmFirefox1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>";
            }




            $("#os_plugin_firefox").html(html2);
            $("#os_plugin_chrome").html("<ol><li>{!!trans('conferences.downloadPluginForWindows')!!} <a href='/vidyo_client_resources/installers/VidyoClientForWeb-win32-1.3.14.0001.msi'>{!!trans('conferences.here')!!}</a>.</li><li>{!!trans('conferences.downloadConfirmChrome1')!!} <a href='/vidyo_client_resources/files/EULA.html' target='_blank'>{!!trans('conferences.downloadConfirm2')!!}</a></li></ol>");
        }

    });
</script>
<body onload="bodyLoaded()">
<body onbeforeunload="return beforeUnload()">

<div class="mycontainerrel" id="goodbye" style="top:130px;">
    <div class="mycontainer3">
        <div class="panel" style="border:2px solid #52b6ec">
            {!!trans('conferences.webDisconnected1')!!} <span
                    style="text-decoration: underline;">{!!trans('conferences.webDisconnected2')!!}
        </div>
    </div>
</div>

<div class="options_menu" id="chat_side_bar">
    <h3>{{trans('conferences.chat')}}</h3>
    <hr>
    <div class="sidebarlist">
        <div id="part_list2">
        </div>
        <button id="back_to_group_chat" class="btn btn-success" style="display: none;" onclick="back_to_group_chat()">
            <span class="glyphicon glyphicon-chevron-left"></span> {{trans('conferences.return')}}</button>
        <audio id="new_message" preload="auto">
            <source src="/vidyo_client_resources/sounds/new_message.mp3"/>
        </audio>
        <audio id="parts_changed" preload="auto">
            <source src="/vidyo_client_resources/sounds/parts_changed.mp3"/>
        </audio>
        <hr>
        <div id="group_chat">
            <span class="label label-primary" style="font-size:16px;">{{trans('conferences.groupChat')}}</span>
            <hr/>
            <div class="group_chat_c" id="group_chat_t"></div>
            <div class="col-md-12">
                <textarea class="send_message" id="send_message"
                          placeholder="{!!trans('conferences.sendMessage')!!}"></textarea>
                <button class="send_message_button" onclick="send_group_message()"
                        title="{{trans('conferences.sendMessage')}}">
                    <img src="/vidyo_client_resources/images/send_message.jpg">
                </button>
            </div>
        </div>
        <div id="private_chat_modals">
        </div>
    </div>

</div>
<div class="options_menu" id="options_menu">
    <h3>{{trans('conferences.settings')}}</h3>
    <hr>
    <div class="sidebarlist">
        <div id="part_list">
        </div>
    </div>
    <hr>
    <div class="sharesection">
        <h4>{{trans('conferences.presentationmode')}}</h4>
        <label for="presentationMode" style="width:70%" id="presentationModetext">
            &nbsp;{!!trans('conferences.presentationmodeDisabled')!!}</label>
        <input type="checkbox" class="presentationMode" name="presentationswitch" id="presentationMode">

    </div>
    <hr>
    <div class="sharesection">
        <h4>{{trans('conferences.windowShare')}}</h4>

        <div class="sidebaricons" id="sharebutton">

            <img id="stopshare" height="20px" src="/vidyo_client_resources/images/share_icon.png">

        </div>
        <select id="img_share_b" class="selectContainer sidebarselect"
                onmouseenter="updateShareList()"
                onchange="shareChanged(this.value); this.selectedindex = -1"
                title="{{trans('conferences.startSharing')}}">
        </select>


    </div>
    <hr>

    <h4>{{trans('conferences.devices')}}</h4>

    <div class="fields">
        <div class="sidebaricons">
            <img height="20px" src="../../vidyo_client_resources/images/camera.png">
        </div>
        <div class="selectContainer sidebarselect">
            <select id="cameraFieldin" onchange="cameraChanged(this.value);"
                    class="large ng-pristine ng-valid sidebarselect " title="{{trans('conferences.camera')}}"></select>
        </div>
        <div class="sidebaricons">
            <img height="20px" src="../../vidyo_client_resources/images/video.png">
        </div>
        <div class="selectContainer sidebarselect">
            <select id="cameraQualityin" onchange="videoQualityChanged(this.value);"
                    class="large ng-pristine ng-valid sidebarselect "
                    title="{{trans('conferences.videoQuality')}}"></select>
        </div>
        <hr>
        <div class="sidebaricons">
            <img height="20px" src="../../vidyo_client_resources/images/mic.png">
        </div>
        <div class="selectContainer sidebarselect">
            <select id="microphoneFieldin" onchange="micChanged(this.value);"
                    class="large ng-pristine ng-valid sidebarselect " title="{{trans('conferences.mic')}}"></select>
        </div>
        <div class="sidebaricons">
            <img height="20px" src="../../vidyo_client_resources/images/speaker.png">
        </div>
        <div class="selectContainer sidebarselect">
            <select id="speakersFieldin" onchange="speakerChanged(this.value);"
                    class="large ng-pristine ng-valid sidebarselect "
                    title="{{trans('conferences.speakers')}}"></select>
        </div>
    </div>
    <hr>
    <h4>{{trans('conferences.otherSettings')}}</h4>
    <hr>
    <label for="participantsname" style="width:70%">{{trans('conferences.showParticipantNames')}}:</label>
    <input type="checkbox" onchange="showpartnameChanged(this.checked);" id="participantsname">


    <label for="EchoCancelation" style="width:70%">{{trans('conferences.echoCancellation')}}:</label>
    <input type="checkbox" onchange="echocancelChanged(this.checked);" id="EchoCancelation">

    <label for="AudioAgc" style="width:70%">{{trans('conferences.audioAGC')}}:</label>
    <input type="checkbox" class="sidebarselect" onchange="AudioAGCChanged(this.checked);" id="AudioAgc">
</div>


<div class="mypanel" id="checking_compatibility" style="margin-top:20px;">
    <div class="collapse navbar-collapse" style="margin:20px; text-align:center;">
        <img src="/images/epresence-logo-sm.png">
    </div>

    <div id="dash" style="margin:20px; text-align:center;">
        <div class="circle"></div>
        <div class="circle1"></div> {{trans('conferences.compatibilityChecking')}}...
    </div>


</div>
<div class="mycontainer3" id="not_supported" style="display:none;">
    <div class="panel" style="text-align: left; padding: 30px;">
        {!!trans('conferences.browserNotSupported')!!}
        <ul>
            <li style="color:#535353;">Windows® 7 32-bit and 64-bit</li>
            <li style="color:#535353;">Windows 8 32-bit and 64-bit</li>
            <li style="color:#535353;">Windows 8.1 32-bit and 64-bit</li>
            <li style="color:#535353;">Windows 10 32-bit and 64-bit</li>
            <li style="color:#535353;">Mac® OS X 10.8 – 10.12</li>
        </ul>
        <h4>{{trans('conferences.supportedBrowser')}}:</h4>
        <ul>
            <li style="color:#535353;">ChromeTM version 48</li>
            <li style="color:#535353;">Firefox® version 52 ESR</li>
            <li style="color:#535353;">Internet Explorer® version 11</li>
            <li style="color:#535353;">Safari® version 9.0.3</li>
        </ul>
    </div>
</div>
<div class="mycontainer" id="whole">
    <div class="videoWrapperFull" id="VidyoSplash" align="center">

        <div class="mycontainer3" id="ready" style="margin-top:20px;">
            <div class="collapse navbar-collapse" style="margin:20px; text-align:center;">
                <img src="/images/epresence-logo-sm.png">
            </div>
            <button class="joinbutton" onclick="sendMyGuestLogin(config)">
                {{trans('conferences.enter')}}
            </button>
            <div id="js-progressbar-container" style="margin:20px;">
                <div id="progressbar"></div>
            </div>
            <?php
            $user_agent_code = Auth::user()->getUserAgent();
            if ($user_agent_code !== 'windows_ie') {
                echo ' <div class="mycontainer3"><div class="panel" id="message" style="text-align: left; padding: 30px;">';
                echo trans('conferences.' . DB::table('settings')->where('category', 'messages')->where('title', $user_agent_code)->value('option'));
                echo '</div></div>';
            }
            ?>
            <fieldset class="settings panel">
                {!!trans('conferences.webClientNotes')!!}
                <br/>
                <hr>
                <h3>{{trans('conferences.avSettings')}}</h3>

                <div class="fields">
                    <div class="labeled field">
                        <label for="speakersField">{{trans('conferences.vidyoproxy')}}</label>
                        <input type="checkbox" id="usevideoProxy">
                    </div>
                    <hr>
                    <div class="labeled field">
                        <label for="cameraField">{{trans('conferences.camera')}}:</label>

                        <div class="selectContainer">
                            <select id="cameraField" class="large ng-pristine ng-valid"></select>
                        </div>
                    </div>
                    <div class="labeled field">
                        <label for="cameraQuality">{{trans('conferences.videoQuality')}}:</label>

                        <div class="selectContainer">
                            <select id="cameraQuality" class="large ng-pristine ng-valid"></select>
                        </div>
                    </div>

                    <hr>
                    <div class="labeled field">
                        <label for="microphoneField">{{trans('conferences.mic')}}:</label>

                        <div class="selectContainer">
                            <select id="microphoneField" class="large ng-pristine ng-valid"></select>
                        </div>
                    </div>
                    <div class="labeled field">
                        <label for="speakersField">{{trans('conferences.speakers')}}:</label>

                        <div class="selectContainer">
                            <select id="speakersField" class="large ng-pristine ng-valid"></select>
                        </div>
                    </div>
                </div>

            </fieldset>

        </div>

    </div>
    <div id="plgPermission" align="left" style="display: none;">
        <div class="mycontainer3">
            <div class="panel" id="message" style="text-align: left; padding: 30px; margin-top: 200px;">
                <h4>{{trans('conferences.firefoxSafariEnablePlugin')}}</h4>
                {!!trans('conferences.firefoxSafariNotesSteps')!!}
                <div class="error-actions" style="text-align:center;">
                    <a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                        {{trans('conferences.homePage')}}</a><a href="mailto:support@epresence.grnet.gr"
                                                                class="btn btn-default btn-lg"><span
                                class="glyphicon glyphicon-envelope"></span> {{trans('conferences.supportContact')}}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="videoWrapperNone" id="ExtensionInstall" align="center" style="display: none;">
        <div class="step">

            <div class="row"><h3>{{trans('conferences.installVW1')}} <span class="main-text">Vidyoweb</span> {{trans('conferences.installVW2')}}</h3></div>
            <div class="row"><p>{{trans('conferences.installVW3')}}</p></div>
            <div class="row">
                <!-- ngIf: isUsingChrome -->
                <button type="button" onclick="installExtension()">{{trans('conferences.installation')}}</button>
                <!-- end ngIf: isUsingChrome -->
            </div>
        </div>
        <div class="error-actions" style="text-align:center; margin-top:20px; ">
            <a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                {{trans('conferences.homePage')}} </a><a href="mailto:support@epresence.grnet.gr"
                                                         class="btn btn-default btn-lg"><span
                        class="glyphicon glyphicon-envelope"></span> {{trans('conferences.supportContact')}} </a>
        </div>
    </div>
    <div id="usage" align="left" style="display: none;">
        <h3 style="margin-bottom: 30px;">
            {{trans('conferences.confirmHtpps')}}
        </h3>
        <br/>

        <div class="error-actions" style="text-align:center;">
            <a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                {{trans('conferences.homePage')}} </a><a href="mailto:support@epresence.grnet.gr"
                                                         class="btn btn-default btn-lg"><span
                        class="glyphicon glyphicon-envelope"></span> {{trans('conferences.supportContact')}} </a>
        </div>
    </div>
    <div class="videoWrapperNone" id="VidyoInstall" align="left" style="display: none;">
        <div class="mycontainer3">
            <div class="panel" id="message" style="text-align: left; padding: 30px;">
                <div id="os_plugin_firefox"></div>
                <br/>

                <div class="error-actions" style="text-align:center;">
                    <a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                        {{trans('conferences.homePage')}} </a><a href="mailto:{{env('SUPPORT_MAIL')}}"
                                                                 class="btn btn-default btn-lg"><span
                                class="glyphicon glyphicon-envelope"></span> {{trans('conferences.supportContact')}}
                    </a>
                </div>
                <br/>

                <p>
                    <small>&copy; 2013-2016 <a href='http://www.vidyo.com'>Vidyo</a>. All rights reserved.</small>
                </p>
            </div>
        </div>
    </div>

    <div class="videoWrapperNone" id="VidyoChromeInstall" align="left" style="display: none;">
        <div class="mycontainer3">
            <div class="panel" id="message" style="text-align: left; padding: 30px;">
                <div id="os_plugin_chrome"></div>
                <br/>

                <div class="error-actions" style="text-align:center;">
                    <a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
                        {{trans('conferences.homePage')}} </a><a href="mailto:{{env('SUPPORT_MAIL')}}"
                                                                 class="btn btn-default btn-lg"><span
                                class="glyphicon glyphicon-envelope"></span> {{trans('conferences.supportContact')}}
                    </a>
                </div>
                <br/>

                <p>
                    <small>&copy; 2013-2016 <a href='http://www.vidyo.com'>Vidyo</a>. All rights reserved.</small>
                </p>
            </div>
        </div>
    </div>

    <div class="videoWrapperSmall" id="VidyoArea" align="center">
        <div class="videoWrapperSmall" id="pluginHolder" align="center"></div>
    </div>
    <div class="videotoolbar" id="hover_buttons">
        <div class="col-md-2 col-lg-2 col-xs-2">
            <button class="videotoolbarbutton" id="img_speaker_b" style="float:left;" onclick="toggleSpeakerIcon()"
                    title="{{trans('conferences.speakers')}}">
                <img id="img_speaker" height="15px" src="/vidyo_client_resources/images/speaker.png">

            </button>
            <div id="sliders" class="sliderbar"></div>
        </div>
        <div class="col-md-2 col-lg-2 col-xs-2">

            <button class="videotoolbarbutton" id="img_mic_b" style="float:left;" onclick="toggleMicIcon()"
                    title="{{trans('conferences.mic')}}">
                <img id="img_mic" height="15px" src="/vidyo_client_resources/images/mic.png">
            </button>
            <div id="sliderm" class="sliderbar"></div>
            <canvas id="meter" width="100" height="12" style="float:left; padding-left:10px;"></canvas>
        </div>
        <div class="col-md-2 col-lg-2 col-xs-2">
            <button class="videotoolbarbutton" id="img_camera_b" style="float:left;" onclick="toggleCameraIcon()"
                    title="{{trans('conferences.camera')}}">
                <img id="img_camera" height="15px" src="/vidyo_client_resources/images/camera.png">
            </button>
            <div style="float:right;">
                <img height="20px" src="/vidyo_client_resources/images/tb_timer.png" style="float:left">

                <div id="timer" class="timer" style="float:right;"></div>
            </div>
        </div>
        <div class="col-md-3 col-lg-4 col-xs-4">
            <button id="img_disconnect_b" style="float:right;" class="videotoolbarbutton" onclick="sendLeaveEvent()"
                    title="{{trans('conferences.disconnect')}}">
                <img id="img_disconnect" height="15px" src="/vidyo_client_resources/images/disconnect.png">
            </button>
            <button id="img_settings" style="float:right;" class="videotoolbarbutton" onclick="toggleSidebar()"
                    title="{{trans('conferences.settings')}}">
                <img height="15px" src="/vidyo_client_resources/images/settings_icon.png">
            </button>
            <button id="img_chat" style="float:right;" class="videotoolbarbutton" onclick="toggleChatBar()"
                    title="{{trans('conferences.chat')}}">
                <img height="15px" src="/vidyo_client_resources/images/chat.png">
            </button>
            <button id="img_layout" style="float:right;" class="videotoolbarbutton" onclick="togglepreviewmode()"
                    title="{{trans('conferences.layout')}}">
                <img height="15px" src="/vidyo_client_resources/images/layout.png">
            </button>
            <button id="img_fullscreen" style="float:right;" class="videotoolbarbutton"
                    onclick="launchIntoFullscreen(document.documentElement)"
                    title="{{trans('conferences.fullScreen')}}">
                <img height="15px" src="/vidyo_client_resources/images/full_screen.png">
            </button>
        </div>
        <div class="col-md-2 col-lg-2 col-xs-2"><img src="/images/client_logo.png"></div>
    </div>
</div>

</body>

</html>
