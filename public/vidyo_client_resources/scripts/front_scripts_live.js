/**
 * Created by tango on 1/19/2017.
 */

$(document).ready(function () {


    start_loading();

    $("body").on("keydown", "#send_message", function (event) {
        if (event.which == 13) {
            event.preventDefault();
            send_group_message();
        }
    });


    $("body").on("keydown", "[id^=SendPrivateMessage]", function (event) {
        var id = $(this).attr('id').split('-').pop(-1);
        if (event.which == 13) {
            event.preventDefault();
            send_priv_message(id);
        }
    });

    $("#sliders").slider({
        range: "min",
        min: 0,
        max: 65535,


        change: function (event, ui) {
        }
    });


    $("#sliders").on("slidechange", function (event, ui) {

        var request = {

            'type': "RequestSetVolumeAudioOut",
            'volume': $("#sliders").slider("option", "value")

        };
        vidyoClient.sendRequest(request);
    });

    $("#sliderm").slider({
        range: "min",
        min: 0,
        max: 65535,
        change: function (event, ui) {
        }
    });


    $("#sliderm").on("slidechange", function (event, ui) {
        //alert('Mic Volume Has Changed');
        var request = {

            'type': "RequestSetVolumeAudioIn",
            'volume': $("#sliderm").slider("option", "value")

        };
        vidyoClient.sendRequest(request);
    });


});

function animate_button(id) {
    $(id).animate({
        backgroundColor: "rgb(160, 210, 105)"
    }, 1000);
    $(id).animate({
        backgroundColor: "#2E2E2E"
    }, 1000);

    $(id).animate({
        backgroundColor: "rgb(160, 210, 105)"

    }, 1000);
    $(id).animate({
        backgroundColor: "#2E2E2E"

    }, 1000);

}

function launchIntoFullscreen(element) {

    if ((document.fullScreenElement !== undefined && document.fullScreenElement === null) || (document.msFullscreenElement !== undefined && document.msFullscreenElement === null) || (document.mozFullScreen !== undefined && !document.mozFullScreen) || (document.webkitIsFullScreen !== undefined && !document.webkitIsFullScreen)) {
        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }
    }
    else {

        if (document.cancelFullScreen) {
            document.cancelFullScreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitCancelFullScreen) {
            document.webkitCancelFullScreen();
        } else if (document.msExitFullscreen) {
            document.msExitFullscreen();
        }

    }
}