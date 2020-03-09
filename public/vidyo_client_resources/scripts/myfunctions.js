var sendMyGuestLogin = function (config) {
    InitialClientConfig();
    var portalUri = config.roomurl;
    if (!portalUri) {
        return 0;
    }
    var portalRes;
    var roomKey;
    var temp = decodeURIComponent(portalUri);
    portalRes = temp.split("\/flex.html\?roomdirect.html&key=");
    portalUri = portalRes[0];
    roomKey = portalRes[1];
    portalRes = portalUri.split("\/flex.html\?roomdirect.html");
    portalUri = portalRes[0];
    var guestName = config.guestname;
    if (!guestName) {
        guestName = "Guest";
    }
    guestName = guestName.replace("+", " ");
    //  var roomPin = credentials["roomPin"];
    var roomPin = config.roompin;
    if (roomPin) {
        var inEvent = {
            'type': "PrivateInEventVcsoapGuestLink",
            'typeRequest': "GuestLink",
            'requestId': 1234,
            'portalUri': portalUri,
            'roomKey': roomKey,
            'pin': roomPin,
            'guestName': guestName
        };
    } else {
        var inEvent = {
            'type': "PrivateInEventVcsoapGuestLink",
            'typeRequest': "GuestLink",
            'requestId': 1234,
            'portalUri': portalUri,
            'roomKey': roomKey,
            'guestName': guestName
        };
    }
    vidyoClient.sendEvent(inEvent);
    beginProgressBar();
}

//PARTICIPANTS LISTS
function updateParticipantsList() {

    var request = {
        'type': "RequestGetParticipants"

    };
    vidyoClient.sendRequest(request, updateParticipantsListwindow);
}
function updateParticipantsListwindow(response) {
    var reply = response.name;
    var html = '<p></p><br/><ul>';
    if (locale === 'el') {
        var html2 = '<h4>Συμμετέχοντες: ' + reply.length + '</h4><br/><ul>';
    }
    else if (locale === 'en') {
        var html2 = '<h4>Participants: ' + reply.length + '</h4><br/><ul>';
    }
    var i;
    var chat = "";
    for (i = 0; i < reply.length; i++) {
        var replyres = reply[i];
        html2 += '<li><span>' + replyres + '</span></li>';
        if (reply[i] !== guestname) {
            html += '<li><span>' + replyres + '</span><button id = "par_btn' + i + '"class="chat_button"  onclick="show_prv_chat(' + i + ')"><span id = "par_spn' + i + '"class="chat_span" style="font-style:italic;"></span></button></li>';
            if (locale === 'el') {
                chat += '<div class="private_chat_modals" id = "chat_modal' + i + '" ><span class="label label-primary" style="font-size:16px;">' + replyres + '</span><hr/><div id="text_area' + i + '" class="group_chat_c"></div><div class="col-md-12"><textarea class ="send_message" id="SendPrivateMessage-' + i + '" placeholder="Αποστολή Μηνύματος" ></textarea><button class="send_message_button" id="btn_priv' + i + '" onclick="send_priv_message(' + i + ')"><img src="/vidyo_client_resources/images/send_message.jpg"></button></div></div>';
            }
            else if (locale === 'en') {
                chat += '<div class="private_chat_modals" id = "chat_modal' + i + '" ><span class="label label-primary" style="font-size:16px;">' + replyres + '</span><hr/><div id="text_area' + i + '" class="group_chat_c"></div><div class="col-md-12"><textarea class ="send_message" id="SendPrivateMessage-' + i + '" placeholder="Send a message" ></textarea><button class="send_message_button" id="btn_priv' + i + '" onclick="send_priv_message(' + i + ')"><img src="/vidyo_client_resources/images/send_message.jpg"></button></div></div>';
            }
        }
    }
    $("#private_chat_modals").html(chat);
    html += '</ul>';
    $("#part_list").html(html2);
    $("#part_list2").html(html);
}

function getconfig() {
    var request = {
        'type': "RequestGetConfiguration"
    };
    vidyoClient.sendRequest(request);
}

function alertconfig(response) {
    console.log("************ RESPONSE ************");
    console.log(response);
}

function toggleoptions(p) {
    var request = {
        'type': "RequestSetConfiguration",
        'enableFullDisplaySharing': p
    };
    vidyoClient.sendRequest(request, showresponse);
}
function showConfigLog() {
    var request = {
        'type': "RequestGetConfiguration"
    };
    vidyoClient.sendRequest(request, alertconfig);
}

function InitialClientConfig() {
    var camSelected = document.getElementById("cameraField").value;
    var speakerSelected = document.getElementById("speakersField").value;
    var micSelected = document.getElementById("microphoneField").value;
    var vidqualitySelected = document.getElementById("cameraQuality").value;

    if ($("#usevideoProxy").prop('checked'))
        vidproxy = 1;
    else
        vidproxy = 0;

    var fields = {
        currentCamera: camSelected,
        currentSpeaker: speakerSelected,
        currentMicrophone: micSelected,
        videoPreferences: vidqualitySelected,
        selfViewLoopbackPolicy: 1,
        enableForceProxy: vidproxy
    };

    vidyoClient.sendRequest({type: 'RequestGetConfiguration'}, function (response) {
        var setRequest = {};
        setRequest.type = 'RequestSetConfiguration';
        for (var key in response) {
            if (key !== 'type' && response.hasOwnProperty(key)) {
                setRequest[key] = response[key];
            }
        }
        for (key in fields) {
            if (fields.hasOwnProperty(key)) {
                setRequest[key] = fields[key];
                if (key === 'currentCamera') {
                    //var cameras = response['cameras'];
                    //var currentCamera = setRequest['currentCamera'];
                    //var vidyoCameraIndex = cameras.indexOf('WebPluginVirtualCamera');
                    //	if (vidyoCameraIndex > - 1 && currentCamera >= vidyoCameraIndex) {
                    //	setRequest.currentCamera = setRequest['currentCamera'] + 1;
                    //	}
                }
            }
        }
        setRequest.currentSpeaker = Math.max(0, setRequest.currentSpeaker);
        setRequest.currentMicrophone = Math.max(0, setRequest.currentMicrophone);
        setRequest.currentCamera = Math.max(0, setRequest.currentCamera);
        vidyoClient.sendRequest(setRequest, function (response) {

        });
    });
}

function vidyoClientConfig(fields) {
    vidyoClient.sendRequest({type: 'RequestGetConfiguration'}, function (response) {
        var setRequest = {};
        setRequest.type = 'RequestSetConfiguration';
        for (var key in response) {
            if (key !== 'type' && response.hasOwnProperty(key)) {
                setRequest[key] = response[key];
            }
        }
        for (key in fields) {
            if (fields.hasOwnProperty(key)) {
                setRequest[key] = fields[key];
                if (key === 'currentCamera') {
                    //var cameras = response['cameras'];
                    //var currentCamera = setRequest['currentCamera'];
                    //var vidyoCameraIndex = cameras.indexOf('WebPluginVirtualCamera');
                    //	if (vidyoCameraIndex > - 1 && currentCamera >= vidyoCameraIndex) {
                    //	setRequest.currentCamera = setRequest['currentCamera'] + 1;
                    //	}
                }
            }
        }
        setRequest.currentSpeaker = Math.max(0, setRequest.currentSpeaker);
        setRequest.currentMicrophone = Math.max(0, setRequest.currentMicrophone);
        setRequest.currentCamera = Math.max(0, setRequest.currentCamera);
        vidyoClient.sendRequest(setRequest, function (response) {

        });
    });
}

function togglehoverbuttons() {
    update_current_slider_values();
    startTimer();
    var cur_state = document.getElementById('hover_buttons').style.visibility;
    if (cur_state == 'hidden' || cur_state == '')
        document.getElementById('hover_buttons').style.visibility = 'visible';
    else {
        document.getElementById('hover_buttons').style.visibility = 'hidden';
    }
}
//VOLUME SLIDERS
function update_current_slider_values() {
    var request = {
        'type': "RequestGetVolumeAudioOut"
    };
    vidyoClient.sendRequest(request, change_current_speaker_volume);
    var request2 = {
        'type': "RequestGetVolumeAudioIn"
    };
    vidyoClient.sendRequest(request2, change_current_mic_volume);
}

function change_current_speaker_volume(response) {
    $("#sliders").slider("value", response.volume);
    //  var value = $( "#sliders" ).slider( "option", "value" );
    //  alert(response.toSource());
}

function change_current_mic_volume(response) {
    $("#sliderm").slider("value", response.volume);
    //  var value = $( "#sliders" ).slider( "option", "value" );
    //alert(response.toSource());
}

function updateConfiglist() {
    log('updateConfiglist()');
    var request = {
        'type': "RequestGetConfiguration"
    };
    vidyoClient.sendRequest(request, updateConfiglistfront);
}
function updateConfiglistfront(response) {
    // CAMERA LIST
    var cameralist = response.cameras;
    var cameralistarrayLength = cameralist.length;
    var htmlcameralist = "";
    for (var i = 0; i < cameralistarrayLength; i++) {
        if (response.currentCamera === i) {
            htmlcameralist += '<option value="' + i + '" selected="selected">' + cameralist[i] + '</option>';
        }
        else {
            htmlcameralist += '<option value="' + i + '" >' + cameralist[i] + '</option>';
        }
    }
    document.getElementById('cameraField').innerHTML = htmlcameralist;
    document.getElementById('cameraFieldin').innerHTML = htmlcameralist;

    //MICROPHONES LIST
    var miclist = response.microphones;
    var miclistarrayLength = miclist.length;
    var htmlmiclist = "";
    for (var i = 0; i < miclistarrayLength; i++) {
        if (response.currentMicrophone === i) {
            htmlmiclist += '<option value="' + i + '" selected="selected">' + miclist[i] + '</option>';
        }
        else {
            htmlmiclist += '<option value="' + i + '">' + miclist[i] + '</option>';
        }
    }
    document.getElementById('microphoneField').innerHTML = htmlmiclist;
    document.getElementById('microphoneFieldin').innerHTML = htmlmiclist;
    //SPEAKERS LIST
    var speakerslist = response.speakers;
    var speakerslistarrayLength = speakerslist.length;
    var htmlspeakerslist = "";
    for (var i = 0; i < speakerslistarrayLength; i++) {
        if (response.currentSpeaker === i) {
            htmlspeakerslist += '<option value="' + i + '" selected="selected">' + speakerslist[i] + '</option>';
        }
        else {
            htmlspeakerslist += '<option value="' + i + '">' + speakerslist[i] + '</option>';
        }
    }
    document.getElementById('speakersField').innerHTML = htmlspeakerslist;
    document.getElementById('speakersFieldin').innerHTML = htmlspeakerslist;

    //VIDEO QUALITY LIST
    var qualitylist = {
        "BestQuality": "Best Quality (Recommended)",
        "BestFramerate": "Best Frame Rate",
        "BestResolution": "Best Resolution",
        "LimitedBandwidth": "Limited Bandwidth",
        "Advanced450p30": "Advanced: 450p30",
        "Advanced720p15": "Advanced: 720p15",
        "Advanced720p30": "Advanced: 720p30"
    };
    var currentQuality = response.videoPreferences;
    var keys = [];
    var htmlqualitylist = "";
    for (var key in qualitylist) {
        if (currentQuality === key) {
            htmlqualitylist += '<option value="' + key + '" selected="selected">' + qualitylist[key] + '</option>';
        }
        else {
            htmlqualitylist += '<option value="' + key + '">' + qualitylist[key] + '</option>';
        }

    }
    document.getElementById('cameraQuality').innerHTML = htmlqualitylist;
    document.getElementById('cameraQualityin').innerHTML = htmlqualitylist;

    var currentecho = response.enableEchoCancellation;
    if (currentecho === 1) {
        document.getElementById('EchoCancelation').setAttribute("checked", "checked");
    } else if (currentecho === 0) {
        document.getElementById('EchoCancelation').removeAttribute("checked");
    }

    var currpartname = response.enableShowConfParticipantName;
    if (currpartname === 1) {
        document.getElementById('participantsname').setAttribute("checked", "checked");
    } else if (currpartname === 0) {
        document.getElementById('participantsname').removeAttribute("checked");
    }

    var curenableAudioAGC = response.enableAudioAGC;
    if (curenableAudioAGC === 1) {
        document.getElementById('AudioAgc').setAttribute("checked", "checked");
    } else if (curenableAudioAGC === 0) {
        document.getElementById('AudioAgc').removeAttribute("checked");
    }
}

function cameraChanged(id) {
    var fields = {
        currentCamera: id
    };
    vidyoClientConfig(fields);
}

function videoQualityChanged(id) {
    var fields = {
        'videoPreferences': id
    };
    vidyoClientConfig(fields);
}

function micChanged(id) {
    var fields = {
        'currentMicrophone': id
    };
    vidyoClientConfig(fields);
}

function speakerChanged(id) {
    var fields = {
        'currentSpeaker': id
    };
    vidyoClientConfig(fields);
}

var AudioAGCChanged = function (id) {
    id = id.toString();
    if (id === "true") {
        par = true;
    }
    else {
        par = false;
    }
    var inEvent = {
        'type': "InEventSetAGC"
    };
    inEvent.enable = par;
    vidyoClient.sendEvent(inEvent);
};

function showpartnameChanged(id) {

    id = id.toString();
    if (id === "true") {
        par = 1;
    }
    else {
        par = 0;
    }
    var fields = {
        'enableShowConfParticipantName': par
    };
    vidyoClientConfig(fields);
}

var echocancelChanged = function (id) {
    id = id.toString();
    if (id == "true") {
        par = true;
    }
    else {
        par = false;
    }
    var inEvent = {
        'type': "InEventSetEchoCancellation"
    };
    inEvent.enable = par;
    vidyoClient.sendEvent(inEvent);
};

var freezeimage = function () {	//FREEZE IMAGE
    var inEvent = {
        'type': "InEventSetFreezeImage"
    };
    inEvent.freeze = true;
    vidyoClient.sendEvent(inEvent);
};

function toggleSidebar() {
    updateConfiglist();
    var url = window.location.href;
    var res = url.split("/");
    var cur_state2 = '';
    cur_state2 = document.getElementById('chat_side_bar').style.display;
    var cur_state = document.getElementById('options_menu').style.display;
    if (cur_state === 'none' || cur_state === "") {

        if (cur_state2 === 'inline') {
            $("#img_chat").css('color', '');
            document.getElementById('chat_side_bar').style.display = 'none';
        }
        document.getElementById('hover_buttons').style.width = '78%';
        document.getElementById('whole').style.width = '78%';
        document.getElementById('whole').style.right = '0px';
        $("#img_settings").css('color', '#a0d269');
        document.getElementById('options_menu').style.display = 'inline';
        document.getElementById('whole').style.top = '4px';

    }
    else {
        $("#img_settings").css('color', '');
        document.getElementById('hover_buttons').style.width = '100%';
        document.getElementById('whole').style.width = '100%';
        document.getElementById('options_menu').style.display = 'none';
        document.getElementById('whole').style.right = '';
        document.getElementById('whole').style.margin = '0px';
        document.getElementById('whole').style.padding = '0px';
        document.getElementById('whole').style.top = '0px';
    }

}
function toggleChatBar() {
    var cur_state = document.getElementById('chat_side_bar').style.display;
    var cur_state2 = document.getElementById('options_menu').style.display;
    if (cur_state === 'none' || cur_state === "") {
        if (cur_state2 === 'inline') {
            $("#img_settings").css('color', '');
            document.getElementById('options_menu').style.display = 'none';
        }
        document.getElementById('hover_buttons').style.width = '78%';
        document.getElementById('whole').style.width = '78%';
        document.getElementById('whole').style.right = '0px';
        document.getElementById('chat_side_bar').style.display = 'inline';

        $("#img_chat").css('color', '#a0d269');
        document.getElementById('whole').style.top = '4px';
        document.getElementById('chat_side_bar').style.top = '4px';

    }
    else if (cur_state === 'inline') {
        document.getElementById('hover_buttons').style.width = '100%';
        document.getElementById('whole').style.width = '100%';
        document.getElementById('chat_side_bar').style.display = 'none';
        $("#img_chat").css('color', '');
        document.getElementById('whole').style.right = '';
        document.getElementById('whole').style.margin = '0px';
        document.getElementById('whole').style.padding = '0px';
        document.getElementById('whole').style.top = '0px';
    }

}


function togglepreviewmode() {

    var request = {
        'type': "RequestGetPreviewMode"
    };
    vidyoClient.sendRequest(request, setpreview);
}

function setpreview(response) {

    console.log("First state:" + response.previewMode);

    if (response.previewMode === 'Dock') {
        setPreviewMode("PIP");
        console.log("Changed to Pip");
    }
    else if (response.previewMode === 'PIP' || response.previewMode === 'Pip') {
        setPreviewMode("None");
        console.log("Changed to None");
    }
    else if (response.previewMode === 'None') {
        setPreviewMode("Dock");
    }
}


function startTimer() {
    var time = 0;
    call_timer = setInterval(function () {
        time++;
        var sec = time % 60;
        var min = (time - sec) / 60 % 60;
        var hour = (time - sec - min * 60) / 3600;
        var str = hour + ':' + ("0" + min).slice(-2) + ':' + ("0" + sec).slice(-2);
        document.getElementById('timer').innerHTML = str;
        document.title = 'In Conference ' + str;
    }, 1000);
}


function onleaving() {
    var url = window.location.href;
    var res = url.split("/");
    if (res[4] !== 'demo-room') {
        document.title = 'Zoom Client';
    }
    else {
        document.title = 'Demo Room';
    }
    if (proxyWrapper.isChrome)
        proxyWrapper.stop();
}

function close_Tab() {
    open(location, '_self').close();
}
function createAudioMeter(audioContext, clipLevel, averaging, clipLag) {
    var processor = audioContext.createScriptProcessor(512);
    processor.onaudioprocess = volumeAudioProcess;
    processor.clipping = false;
    processor.lastClip = 0;
    processor.volume = 0;
    processor.clipLevel = clipLevel || 0.98;
    processor.averaging = averaging || 0.95;
    processor.clipLag = clipLag || 750;

    // this will have no effect, since we don't copy the input to the output,
    // but works around a current Chrome bug.
    processor.connect(audioContext.destination);

    processor.checkClipping =
        function () {
            if (!this.clipping)
                return false;
            if ((this.lastClip + this.clipLag) < window.performance.now())
                this.clipping = false;
            return this.clipping;
        };
    processor.shutdown =
        function () {
            this.disconnect();
            this.onaudioprocess = null;
        };
    return processor;
}

function volumeAudioProcess(event) {
    var buf = event.inputBuffer.getChannelData(0);
    var bufLength = buf.length;
    var sum = 0;
    var x;
    // Do a root-mean-square on the samples: sum up the squares...
    for (var i = 0; i < bufLength; i++) {
        x = buf[i];
        if (Math.abs(x) >= this.clipLevel) {
            this.clipping = true;
            this.lastClip = window.performance.now();
        }
        sum += x * x;
    }
    // ... then take the square root of the sum.
    var rms = Math.sqrt(sum / bufLength);

    // Now smooth this out with the averaging factor applied
    // to the previous sample - take the max here because we
    // want "fast attack, slow release."
    this.volume = Math.max(rms, this.volume * this.averaging);
}

var send_group_message = function () {

    var msg = $("#send_message").val();
    if (msg !== "" && msg !== null && msg !== " " && msg !== "  ") {
        var inEvent = {
            'type': "InEventGroupChat",
            'message': msg
        };
        vidyoClient.sendEvent(inEvent);
        $("#send_message").val("");
        if (locale === 'el') {
            $("#send_message").attr("placeholder", "Αποστολή Μηνύματος");
        } else if (locale === 'en') {
            $("#send_message").attr("placeholder", "Send a message");
        }
        var name = guestname;
        var dt = new Date();
        var html = document.getElementById('group_chat_t').innerHTML;
        var hours = dt.getHours();
        var minutes = dt.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;

        html += '<br/><span style="color:red">' + name + ': </span><span style="color:#d3d3d3">(' + hours + ":" + minutes + ' ' + ampm + ')</span><br/>' + msg + '<br/>';
        document.getElementById('group_chat_t').innerHTML = html;
        var objDiv = document.getElementById("group_chat_t");
        objDiv.scrollTop = objDiv.scrollHeight;
    }
};

function send_priv_message(indx) {

    var request = {
        'type': "RequestGetParticipantStatisticsAt",
        'index': indx

    };
    vidyoClient.sendRequest(request, function (response) {
        //console.log(response.toSource());
        var uri = response.uri;
        var id = "#SendPrivateMessage-" + indx;
        var msg = $(id).val();
        var inEvent = {
            'type': "InEventPrivateChat",
            'message': msg,
            'uri': uri
        };
        vidyoClient.sendEvent(inEvent);
        var dt = new Date();
        var hours = dt.getHours();
        var minutes = dt.getMinutes();
        var ampm = hours >= 12 ? 'pm' : 'am';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = minutes < 10 ? '0' + minutes : minutes;
        var html = $("#text_area" + indx).html();
        html += '<br/><span style="color:red">' + name + ': </span><span style="color:#d3d3d3">(' + hours + ":" + minutes + ' ' + ampm + ')</span><br/>' + msg + '<br/>';

        $(id).val('');
        $("#text_area" + indx).html(html);
        var objDiv = document.getElementById('text_area' + indx);
        objDiv.scrollTop = objDiv.scrollHeight;


    });
}

function show_prv_chat(id) {
    $("#private_chat_modals").show();
    var participants = $('.private_chat_modals').length;
    for (i = 0; i < participants; i++) {
        if (id !== i) {
            $("#chat_modal" + i).hide();
        }
    }
    $("#group_chat").hide();
    $("#chat_modal" + id).show();
    $("#par_spn" + id).html('');
    $("#back_to_group_chat").show();

    var objDiv = document.getElementById('text_area' + id);
    objDiv.scrollTop = objDiv.scrollHeight;
}

function back_to_group_chat() {

    $("#private_chat_modals").hide();

    $("#group_chat").show();
    $("#back_to_group_chat").hide();
    var objDiv = document.getElementById('group_chat_t');
    objDiv.scrollTop = objDiv.scrollHeight;
}

function play_sound(id) {
    var sound = $(id)[0];
    sound.play();

}

function get_user_media() {
    //canvasContext = document.getElementById("meter").getContext("2d");

// monkeypatch Web Audio
    window.AudioContext = window.AudioContext || window.webkitAudioContext;

// grab an audio context
    audioContext = new AudioContext();

// Attempt to get audio input
    try {
        // monkeypatch getUserMedia
        getUserMedia = (navigator.getUserMedia ||
        navigator.webkitGetUserMedia ||
        navigator.mozGetUserMedia);

        // ask for an audio input
        navigator.getUserMedia(
            {
                "audio": {
                    "mandatory": {
                        "googEchoCancellation": "false",
                        "googAutoGainControl": "false",
                        "googNoiseSuppression": "false",
                        "googHighpassFilter": "false"
                    },
                    "optional": []
                },
            }, gotStream, didntGetStream);
    } catch (e) {
        alert('getUserMedia threw exception :' + e);
    }
}
function its_ready() {
    $("#checking_compatibility").hide();
    $("#ready").show();
}

function get_conf_info() {
    var request = {
        'type': "RequestGetCurrentCpuUtilization"
    };
    vidyoClient.sendRequest(request, show_info);
}

function show_info(response) {
    console.log(response);
}

function start_loading() {
    console.log('started_loading');
    $("#dash").show();
}

function stop_loading() {
    console.log('stoped_loading');
    $("#dash").hide();
    $("#whole").css('visibility', 'visible');
}

function EnableAppSharing() {

    var request = {
        'type': "RequestEnableAppShare",
        'isEnable':true
    };

    vidyoClient.sendRequest(request, alertconfig);
}