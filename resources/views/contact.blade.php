@extends('app')

@section('header-javascript')
    <link rel="stylesheet" href="css/font-awesome.css">
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script src="js/jquery-2.1.4.js"></script>
    <script src="bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <!-- <script src="js/main.js"></script> -->


    <script src="js/carousel.js"></script>
    <link rel="stylesheet" href="css/carousel.css">

    <script type="text/javascript">
        $(document).ready(function() {

            $('[data-toggle="tooltip"]').tooltip();

        })
    </script>
@endsection
@section('extra-css')
    <style>
        #main-slider {
            background-image: url(images/slider-epikoinonia.jpg);
        }
        .img-center{
            display: block;
            margin:0 auto;
        }
        .font-counter{
            font-size:28px;
            font-weight:bold;
            color:#fff;
        }
        .counter-small{
            font-size:18px;
            color: #fff;
            padding-top:5px;
        }
    </style>
@endsection
@section('contact-active')
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
            </div><!--/.item-->
        </div><!--/.carousel-inner-->
        <!--      <a class="prev" href="#main-slider" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
              <a class="next" href="#main-slider" data-slide="next"><i class="fa fa-chevron-right"></i></a> -->
    </section><!--/#main-slider-->
    <section id="Index">
        <div class="container">
            <div class="box first" style="padding: 30px 50px">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 style="color:#52B6EC">{{trans('site.contactForm')}}</h3>
                        <hr>
                        <p>{!! trans('site.contactFormInfo')!!}</p>
                        <p>{{trans('site.fillForm')}}:</p>
                        @if ($errors->any())
                            <ul class="alert alert-danger" style="margin: 0px 0px 10px 0px">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                        @if (session()->has('status'))
                            <div class="alert alert-success">
                                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                                {{ session()->get('status') }}
                            </div>
                        @endif

                        {!! Form::open(array('url' => 'contact', 'method' => 'post', 'class' => 'contact-form', 'id' => 'main-contact-form', 'role' => 'form')) !!}
                        {!! Honeypot::generate('my_name', 'my_time') !!}
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    @if (Auth::check())
                                        {!! Form::text('fullname', Auth::user()->firstname.' '.Auth::user()->lastname, ['class' => 'form-control', 'id' => 'fullname', 'placeholder' => trans('site.fullName')]) !!}
                                    @else
                                        {!! Form::text('fullname', null, ['class' => 'form-control', 'id' => 'fullname', 'placeholder' => trans('site.fullName')]) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    @if (Auth::check())
                                        {!! Form::text('email', Auth::user()->email, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'Email']) !!}
                                    @else
                                        {!! Form::text('email', null, ['class' => 'form-control', 'id' => 'email', 'placeholder' => 'Email']) !!}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    {!! Form::textarea('text', null, ['class' => 'form-control', 'id' => 'message', 'placeholder' => trans('site.message'), 'rows' => '8']) !!}
                                </div>
                                <div class="form-group">
                                    <input type="text" name="origin" value="contact" hidden>
                                    {!! Form::submit(trans('site.send'), ['class' => 'btn btn-primary btn-lg', 'id' => 'sendMessage']) !!}
                                </div>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div><!--/.col-sm-6-->
                    <div class="col-sm-1"></div>
                    <div class="col-sm-5">
                        <h3>{{trans('site.address')}}</h3>
                        <hr>
                        <img src="images/gr-net-medium.png" width="250" height="116" class="img-responsive">
                        <address>
                            {!!trans('site.addressGrnet')!!}
                        </address>
                    </div><!--/.col-sm-6-->

                </div><!--/.row-->
            </div><!--/.box-->
        </div><!--/.container-->
    </section>

@endsection