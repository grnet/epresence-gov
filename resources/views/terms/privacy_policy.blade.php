@extends('app')

@section('header-javascript')
    <link rel="shortcut icon" href="/images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/images/ico/apple-touch-icon-57-precomposed.png">
    <script src="/js/jquery-2.1.4.js"></script>
    <link href="/bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
    <script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/bootstrap-calendar/css/calendar.css">
    <link href="/css/main.css" rel="stylesheet">
    <script type="text/javascript">
        $(document).ready(function() {
            $('[data-toggle="tooltip"]').tooltip();
        })
    </script>
@endsection

@section('extra-css')
    <style>
        .container
        {
            min-width: 400px !important;
        }
        .noshadow {
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            border:0px;
        }

    </style>
@endsection
@section('calendar-active')
    class="active"
@endsection
@section('content')
    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px; min-height:200px">
                <h1 style="color:#52B6EC">{{trans('site.privacy_policy')}}</h1>
                <div class="small-gap"></div>
                <hr/>
                {!! trans('terms.privacy_notice') !!}
            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection