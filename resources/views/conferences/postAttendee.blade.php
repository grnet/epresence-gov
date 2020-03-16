@extends('app')

@section('header-javascript')

    <link rel="stylesheet" href="/css/font-awesome.css">

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

@endsection
@section('extra-css')
    <style>

        .seconds {
            display: none;
        }

        .container {
            min-width: 400px !important;
        }

        .noshadow {
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            border: 0;
        }

        .template {
            padding: 40px 15px;
            text-align: center;
        }

        .actions {
            margin-top: 15px;
            margin-bottom: 15px;
        }

        .actions .btn {
            margin-right: 10px;
        }

        ul {
            list-style-type: none;
        }

        li.active {
            color: green;
        }

    </style>
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                <div class="row">
                    <div class="col-md-12">
                        <div class="template">
                            {!! trans('conferences.post_attendee') !!}
                        </div>
                    </div>
                </div><!--/.box-->
            </div><!--/.container-->
        </div>
    </section>
@endsection

