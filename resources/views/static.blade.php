<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>e:Presence</title>

    <link rel="shortcut icon" href="/images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/images/ico/apple-touch-icon-57-precomposed.png">

    <link href="bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
    <link href="css/main.css" rel="stylesheet">

    <link rel="stylesheet" href="css/font-awesome.css">
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script src="js/jquery-2.1.4.js"></script>
    <script src="bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <!-- <script src="js/main.js"></script> -->

    <script>
        $(document).ready(function () {
            // Change user image
            // $("[id^=setLangTo]").on("click", function () {
            //     var lang = $(this).attr('id').split('-').pop(-1);
            //     $.post("/language/change_language", {locale: lang})
            //         .done(function (data) {
            //             obj = JSON.parse(data);
            //             if (obj.status == 'success') {
            //                 window.location.reload(true);
            //             }
            //         })
            //         .fail(function (xhr, textStatus, errorThrown) {
            //             alert(xhr.responseText);
            //         });
            // });


            $("#accept_cookies_consent").on("click", function () {
                setCookie('ePresence_cookie_consent', true, 20 * 365);
                $("#cookie_consent_container").slideUp();
            });

            var myCookie = getCookie("ePresence_cookie_consent");

            if (myCookie == null)
                $("#cookie_consent_container").slideDown();
        });

        function setCookie(name, value, expirationInDays) {
            var date = new Date();
            date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
            document.cookie = name + '=' + value + '; ' + 'expires=' + date.toUTCString() + ';path=/{{ config('session.secure') ? ';secure' : null }}';
        }

        function getCookie(name) {
            var dc = document.cookie;
            var prefix = name + "=";
            var begin = dc.indexOf("; " + prefix);
            if (begin == -1) {
                begin = dc.indexOf(prefix);
                if (begin != 0) return null;
            }
            else {
                begin += 2;
                var end = document.cookie.indexOf(";", begin);
                if (end == -1) {
                    end = dc.length;
                }
            }
            // because unescape has been deprecated, replaced with decodeURI
            //return unescape(dc.substring(begin + prefix.length, end));
            return decodeURI(dc.substring(begin + prefix.length, end));
        }

    </script>
    <style>
        .bottomLinks {
            margin-top: 10px;
        }

        #cookie_consent_container {
            color: #b4b4b4;
            display: none;
            position: fixed;
            width: 100%;
            height: auto;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            padding: 0px 10px 0px 10px;
            z-index: 999999;
            line-height: 5 !important;
            text-align: center;
        }

        #cookie_consent_container span {
            margin-left: 5px;
            margin-right: 5px;
        }

        #accept_cookies_consent {
            border-radius: 5px !important;
        }

        .privacy_policy_table {
            width:100%;
            margin-bottom:10px;
        }
        .privacy_policy_table td , .privacy_policy_table th {
            padding:15px;
        }
    </style>
    <script src="js/carousel.js"></script>
    <link rel="stylesheet" href="css/carousel.css">
    @yield('head-extra')
</head>
<body data-spy="scroll" data-target="#navbar" data-offset="0">
@if(Auth::check() && empty(Auth::user()->accepted_terms) && !\Request::is('account_activation') && !\Request::is('terms') && !\Request::is('privacy_policy'))
    @include('terms.terms_privacy_modal')
@endif
<div class="col-md-12" id="cookie_consent_container">
    <span>{{trans('cookies.cookies_consent')}}</span>
    <a href="/cookies"><span>{{trans('cookies.cookies_learn_more')}}</span></a>
    <span><button class="btn btn-primary" id="accept_cookies_consent">OK</button></span>
</div>
<header id="header" role="banner" style="z-index: 1000;">
    <div class="container">
        <div id="navbar" class="navbar navbar-default">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="/"></a>
            </div>
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    @if (Auth::check())
                        <li @yield('home-active')><a href="/"><span
                                        class="glyphicon glyphicon glyphicon-home"></span></a></li>
                        @if(Auth::user()->hasRole('SuperAdmin'))
                            <li @yield('conference-active')><a
                                        href="/conferences?limit=50&sort_start=asc">{{trans('site.conferences')}}</a>
                            </li>
                        @else
                            <li @yield('conference-active')><a href="/conferences">{{trans('site.conferences')}}</a>
                            </li>
                        @endif
                        @can('view_users_menu')
                            <li @yield('users-active')><a href="/users">{{trans('site.users')}}</a></li>
                        @endcan
                        @can('view_institutions')
                            <li @yield('institutions-active')><a href="/institutions">{{trans('site.institutions')}}</a>
                            </li>
                        @endcan
                        <li @yield('demo-active')><a href="/demo-room">Demo Room</a></li>
                        @if(!Auth::user()->hasRole('SuperAdmin'))
                            <li @yield('support-active')><a href="/support">{{trans('site.support')}}</a></li>
                            <li @yield('contact-active')><a href="/contact">{{trans('site.contact')}}</a></li>
                        @endif
                        <li @yield('statistics-active')><a href="/statistics">{{trans('site.statistics')}}</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <small><span class="glyphicon glyphicon-user"></span></small>
                                <strong>{{ mb_substr(Auth::user()->firstname, 0, 1, 'utf-8').'.'.mb_substr(Auth::user()->lastname, 0, 1, 'utf-8').'.' }}</strong>
                                <small><span class="glyphicon glyphicon-chevron-down"></span></small>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <div class="navbar-login">
                                        <div class="row">
                                            <div class="col-lg-4">
                                                <p class="text-center userImage">
                                                    @if(!empty(Auth::user()->thumbnail))
                                                        <img src="/images/user_images/{{ Auth::user()->thumbnail }}"
                                                             class="img-responsive" alt="Responsive image">
                                                    @else
                                                        <span class="glyphicon glyphicon-user icon-size"></span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-lg-8">
                                                <p class="text-left">
                                                    <strong>{{ Auth::user()->firstname }} {{ Auth::user()->lastname }}</strong>
                                                </p>
                                                <p class="text-left small">{{  trans(Auth::user()->roles()->first()->label) }}</p>
                                                <p class="text-left small">{{ Auth::user()->email }}</p>
                                                <p class="text-left">
                                                    <a href="/account"
                                                       class="btn btn-primary btn-block btn-sm">{{trans('site.myAccount')}}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="divider"></li>
                                <li>
                                    <div class="navbar-login navbar-login-session">
                                        <div class="row">
                                            <div class="col-lg-12">
                                                <p>
                                                    <a href="/auth/logout"
                                                       class="btn btn-danger btn-block">{{trans('site.logout')}}</a>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li @yield('home-active')><a href="/"><span
                                        class="glyphicon glyphicon glyphicon-home"></span></a></li>
                        <li @yield('access-active')><a href="/access">{{trans('site.access')}}</a></li>
                        <li @yield('support-active')><a href="/support">{{trans('site.support')}}</a></li>
                        <li @yield('contact-active')><a href="/contact">{{trans('site.contact')}}</a></li>
                        <li @yield('calendar-active')><a href="/calendar">{{trans('site.calendar')}}</a></li>
                        <li><a href="/login">{{trans('site.login')}}</a></li>
                    @endif
{{--                    <li class="dropdown">--}}
{{--                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">--}}
{{--                            <img src="/images/{{ Session::get('locale') }}.png"/>--}}
{{--                            <strong style="text-transform: uppercase !important;">{{ Session::get('locale') }}</strong>--}}
{{--                            <span class="glyphicon glyphicon-chevron-down"></span>--}}
{{--                        </a>--}}
{{--                        <ul class="dropdown-menu">--}}
{{--                            <li>--}}
{{--                                <a href="#" id="setLangTo-el"><img src="/images/el.png"/> Ελληνικά</a>--}}
{{--                            </li>--}}
{{--                            <li>--}}
{{--                                <a href="#" id="setLangTo-en"><img src="/images/en.png"/> English</a>--}}
{{--                            </li>--}}
{{--                        </ul>--}}
{{--                    </li>--}}
                    @if(Auth::check() && Auth::user()->hasRole('SuperAdmin'))
                       <li><a href="/admin/dashboard" target="_blank">Admin</a></li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</header><!--/#header-->
@yield('content')
@include('footer')
@include('partials.notifications')
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-25972179-1', 'auto');
    ga('send', 'pageview',{
        'anonymizeIp': true
    });
</script>
</body>
</html>