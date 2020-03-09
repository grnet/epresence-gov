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
    
    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    
	<script src="/js/jquery-2.1.4.js"></script>    
      
    <link href="/bootstrap-3.1.1-dist/css/bootstrap.css" rel="stylesheet">
	<script src="/bootstrap-3.1.1-dist/js/bootstrap.min.js"></script>
    
	<!-- checkbox --> 
	<script src="/bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
	<link rel="stylesheet" href="/bootstrap-checkbox-x/checkbox-x.css">
    
    
    
    <link rel="stylesheet" href="/css/font-awesome.css">    

    <link href="/css/main.css" rel="stylesheet">
   
    <style>

	#loginbox {
		/* width: 800px;*/ 
		position: absolute; 
		margin: 0 auto;
		left:0;
		right:0;
		top:50%;
		margin-top: -220px;
		
		
		  display: block;
  	margin-left: auto;
 	 margin-right: auto; 
	}
	
	.ssoRow {
		display: table;
		width:100%;
	}
	.ssoColumn {
		display: table-cell;
		vertical-align:middle;
		text-align:center;
	}
	</style>

   	<script type="text/javascript">
		$(document).ready(function() { 
		
		
			$('[data-toggle="tooltip"]').tooltip();		  
		 
			$("#loginform").submit(function() {
				// $("#SendPassword").hide();
				setTimeout(function() { window.location.href = "/"; }, 7000);
			});
		
		})
	</script>
    
</head><!--/head-->

<body>
	<div class="container-fluid"> 
		<div class="row pull-right">
			<a href="/"><span class="glyphicon glyphicon glyphicon-home" style="margin:20px; font-size:25px" data-toggle="tooltip" data-placement="left" title="{{trans('auth.homePage')}}"></span></a>
		</div>
        
		<div class="row">
			<div id="loginbox" class=" box col-xs-12" style="max-width:350px; padding:20px">
				<div class="row"> 
					<div class="col-sm-12">
                        <img src="/images/epresence-logo.png" class="img-responsive">
                    </div>
                    <div class="col-sm-12">
        				<h3 class=" pull-right" style="margin-top:0px; margin-bottom:25px; color:#666"> {{trans('auth.newPassword')}}</h3>
                  	</div>
                 </div>   
                
                <div class="row">
                    <div class="col-sm-12">
                     <p style="color:#666; margin-bottom:20px" >{!!trans('auth.passwordResetEmail')!!}</p>
					 @if (session('status'))
						<div class="alert alert-success">
								{{ session('status') }}
							</div>
						@endif

						@if (count($errors) > 0)
							<div class="alert alert-danger">
								<ul>
									@foreach ($errors->all() as $error)
										<li>{{ $error }}</li>
									@endforeach
								</ul>
							</div>
						@endif
                    </div>
                </div>
				<form class="form-horizontal" id="loginform" role="form" method="POST" action="{{ url('/password/email') }}">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
						<div style="margin-bottom: 5px;" class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
							<input type="email" class="form-control" name="email" placeholder="Email">
						</div>

						<div class="form-group">
							<div class="col-md-6 col-md-offset-4">
								<button type="submit" id="SendPassword" class="btn btn-success pull-right">
									{{trans('auth.sendEmail')}} <span class="glyphicon glyphicon-chevron-right"></span>
								</button>
							</div>
						</div>
				</form>                    
            </div>      
        </div>
   </div>
</body>
</html>		
