<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>e:Presence</title>


    <link rel="shortcut icon" href="/images/ico/favicon.ico">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="/images/ico/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="/images/ico/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="/images/ico/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="/images/ico/apple-touch-icon-57-precomposed.png">

    <link href="/bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->

    <script src="/js/jquery-2.1.4.js"></script>
    <script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    <!-- <script src="js/main.js"></script> -->

    @yield('header-javascript')

    @yield('extra-css')

</head><!--/head-->
<body data-spy="scroll" data-target="#navbar" data-offset="0">
<header id="header" role="banner" style="z-index:1000">
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
            </div>
        </div>
    </div>
</header><!--/#header-->
@yield('content')
@include('footer')
</body>
</html>
