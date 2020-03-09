@extends('app')

@section('header-javascript')
    <link rel="stylesheet" href="/css/font-awesome.css">
    <!--[if lt IE 9]>
    <script src="/js/html5shiv.js"></script>
    <script src="/js/respond.min.js"></script>
    <![endif]-->

    <script src="/js/jquery-2.1.4.js"></script>
    <script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <!-- <script src="js/main.js"></script> -->


    <script src="/js/carousel.js"></script>
    <link rel="stylesheet" href="/css/carousel.css">

    <script type="text/javascript" src="/flowplayer-5.4.6/flowplayer.min.js"></script>
    <link rel="stylesheet" type="text/css" href="/flowplayer-5.4.6/skin/minimalist.css">

    <script type="text/javascript">
        $(document).ready(function () {

            $('[data-toggle="tooltip"]').tooltip();
        });
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
        </div>
    </section>

    <section>
        <div class="container">
            <div class="box" style="padding: 30px 50px">
                <ul class="nav nav-tabs">
                    <li><a href="/support/faq">Faq</a></li>
                    <li class="active"><a href="#">{!! trans('site.support_manuals') !!}</a></li>
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
                    <div class="row tab-pane active" id="documents">
                        @foreach($documents as $document)
                            @if($document->has_link_attached())
                                <div class="guide col-md-4 col-sm-6">
                                    <div class="center">
                                        <div class="icon-lg icon-user"><i class="fa fa-file-text"></i></div>

                                        @if(App::getLocale() == 'en')
                                            <p>{!! $document->title_en !!}</p>
                                        @else
                                            <p>{!! $document->title_el !!}</p>
                                        @endif

                                        <p class="small-gap">
                                            @if(!empty($document->get_el_download_url()))
                                                <a href="{{$document->get_el_download_url()}}" target="_blank">
                                                    <button class="btn btn-default btn-sm"><i class="fa fa-download"></i> EL
                                                    </button>
                                                </a>
                                            @endif
                                            @if(!empty($document->get_en_download_url()))
                                                <a href="{{$document->get_en_download_url()}}" target="_blank">
                                                    <button class="btn btn-default btn-sm"><i class="fa fa-download"></i> EN
                                                    </button>
                                                </a>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection