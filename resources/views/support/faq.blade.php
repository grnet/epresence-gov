@extends('app')

@section('header-javascript')
    <link rel="stylesheet" href="/css/font-awesome.css">
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->


    <!-- <script src="js/main.js"></script> -->


    <script src="/js/carousel.js"></script>
    <link rel="stylesheet" href="/css/carousel.css">

    <script type="text/javascript" src="/flowplayer-5.4.6/flowplayer.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/flowplayer-5.4.6/skin/minimalist.css">

    <script type="text/javascript">


        var tag = document.createElement('script');

        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);


        $(document).ready(function () {


            // 3. This function creates an <iframe> (and YouTube player)
            //    after the API code downloads.
            var player;

            function open_video_modal(video_id) {

                if (player)
                    player.destroy();

                player = new YT.Player('video_element', {
                    height: '100%',
                    width: '100%',
                    videoId: video_id,
                    host: 'http://www.youtube.com',
                    events: {
                        'onReady': onPlayerReady,
                        'onStateChange': onPlayerStateChange
                    }
                });

            }


            $(".youtube_thumbnails").on("click", function () {
                open_video_modal($(this).attr("data-video-id"));
            });

            $(".play_buttons").on("click", function () {
                open_video_modal($(this).attr("data-video-id"));
            });


            // 4. The API will call this function when the video player is ready.
            function onPlayerReady(event) {

                $("#youtubeModal").modal("show");
                event.target.playVideo();
            }

            // 5. The API calls this function when the player's state changes.
            //    The function indicates that when playing a video (state=1),
            //    the player should play for six seconds and then stop.


            function onPlayerStateChange(event) {

                // if (event.data == YT.PlayerState.PLAYING && !done) {
                //     setTimeout(stopVideo, 6000);
                //     done = true;
                // }
            }

            function stopVideo() {
                player.stopVideo();
            }

            $("#youtubeModal").on("hidden.bs.modal", function () {

                if (player)
                    stopVideo();

            });


            $('[data-toggle="tooltip"]').tooltip();


        });
    </script>
    <script
            src="https://code.jquery.com/ui/1.8.23/jquery-ui.min.js"
            integrity="sha256-sEFM2aY87nr5kcE4F+RtMBkKxBqHEc2ueHGNptOA5XI="
            crossorigin="anonymous"></script>
    <script>
        $( function() {
            $( "#accordion" ).accordion();
        } );
    </script>
@endsection
@section('extra-css')
    <style>
        #main-slider {
            background-image: url(/images/slider-ypostiriksi.jpg);
        }

        .img-center {
            display: block;
            margin: 0 auto;
        }

        .font-counter {
            font-size: 28px;
            font-weight: bold;
            color: #fff;
        }

        .counter-small {
            font-size: 18px;
            color: #fff;
            padding-top: 5px;
        }

        .guide {
            min-height: 250px;
        }

        .youtube_thumbnails {
            max-width: 100%;
            cursor: pointer;
        }

        .video_container {
            height: 500px;
        }

        .video_preview_container .play_buttons {
            width: 60px;
            height: 60px;
            position: absolute;
            left: 0;
            top: 0;
            right: 0;
            bottom: 0;
            cursor: pointer;
            display: block;
            margin: auto;
        }

        #accordion h3 {
            cursor:pointer;
        }

    </style>
@endsection
@section('support-active')
    class="active"
@endsection
@section('content')

    <!--/#main-slider-->
    <section id="main-slider" class="carousel">
        <div class="carousel-inner">
            <div class="item active">
                <div class="container">
                    <div class="carousel-content">
                        <h1>&nbsp;</h1>

                        <p class="lead carousel-shadow">&nbsp;</p>
                    </div>
                </div>
            </div>
            <!--/.item-->
        </div>

    </section><!--/#main-slider-->

    <section>
        <div class="container">
            <div class="box" style="padding: 30px 50px">
                <ul class="nav nav-tabs">
                    <li  class="active"><a href="#">Faq</a></li>
                    @if($total_documents>0)
                    <li><a href="/support/documents">{!! trans('site.support_manuals') !!}</a></li>
                    @endif
                    @if($total_videos>0)
                        <li><a href="/support/videos">{!! trans('site.support_videos') !!}</a></li>
                    @endif
                    <li><a href="/support/teamviewer">Teamviewer</a></li>
                    @if($total_downloads>0)
                    <li><a href="/support/downloads">Downloads</a></li>
                    @endif
                </ul>
                <div class="medium-gap"></div>
                <div class="medium-gap"></div>
                <div class="tab-content">
                    <!--/.row-->
                    <div class="row tab-pane active" id="videos">
                        @foreach($faqs as $faq)
                                @if(App::getLocale() == 'en')
                                    <strong>{!! $faq->en_question !!}</strong>
                                @else
                                    <strong>{!! $faq->el_question !!}</strong>
                                @endif
                                <div style="margin:20px 0 10px 0; border-bottom: 1px solid #ddd;">
                                @if(App::getLocale() == 'en')
                                    <p style="margin:10px 0px 10px 0px;">{!! $faq->en_answer !!}</p>
                                @else
                                    <p style="margin:10px 0px 10px 0px;">{!! $faq->el_answer !!}</p>
                                @endif
                                </div>
                        @endforeach
                    </div>
                    <!--/.tab content-->
                </div>
                <!--/.box-->
            </div>
            <!--/.container-->
        </div>
        <div class="modal fade" id="youtubeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="video_container">
                            <div id="video_element"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection


