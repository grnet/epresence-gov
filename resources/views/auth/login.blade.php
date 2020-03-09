<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title> e:Presence</title>
    
    
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
					<div class="col-sm-12" style="margin-bottom:20px;">
                        <img src="/images/epresence-logo.png" class="img-responsive">
                    </div>
                    <div class="col-sm-12">
        				<h3 class=" pull-right" style="margin-top:0px; margin-bottom:25px; color:#666"> {{trans('auth.entryPoint')}}</h3>
                  	</div>
                 </div>   
                
                <div class="row">
                    <div class="col-sm-12">
                        <a class="btn btn-primary" style="width:100%" href="/login">                         
                            <div class="ssoRow">
                                <div class="ssoColumn"><span class="fa fa-graduation-cap" style="font-size:20px"></span></div>
                                <div class="ssoColumn" style="width:100%;">{!!trans('auth.ssoLogin')!!}</div>
                                <div class="ssoColumn"><span class="glyphicon glyphicon-chevron-right"></span></div>
                            </div>
                        </a>
                    </div>
                </div>  
                
                <div class="row" style="margin-top:15px; margin-bottom:15px;">
                    <div class="col-sm-12">                        
						<div class="ssoRow">
							<div class="ssoColumn" style="width:45%;"><hr></div>
							<div class="ssoColumn" ><span style="color:#999">{{trans('auth.orSelection')}}</span></div>
							<div class="ssoColumn" style="width:45%;"><hr></div>
                        </div>
					</div>
                </div> 

					<form id="loginform" class="form-horizontal" role="form" method="POST" action="{{ url('/auth/login') }}">
						<input type="hidden" name="_token" value="{{ csrf_token() }}">
						
						@if (count($errors) > 0)
							<ul class="alert alert-danger">
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						@endif
                        @if (session()->has('account_deleted_error'))
                            <ul class="alert alert-danger">
                                    <li>{!! session()->get('account_deleted_error') !!}</li>
                            </ul>
                        @endif
						<div style="margin-bottom: 5px;" class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
								<input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email">
						</div>

						<div style="margin-bottom: 5px" class="input-group">
							<span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
							<input id="password" type="password" class="form-control" name="password" placeholder="Password">
						</div>
						
						<div class="row">
                        <div class="col-sm-6">
                            <div class="checkbox" style="padding-left:0px">
								<input id="remember" data-toggle="checkbox-x" data-size="xs" data-three-state="false" name="remember" tyoe="checkbox"/> 
                                <label class="cbx-label" for="remember"><small>{{trans('auth.rememberMe')}}</small></label>
                            </div>
                        </div>
                      
						<div class="col-sm-6">
							<button type="submit" class="btn btn-success pull-right">{{trans('auth.entryPoint')}} <span class="glyphicon glyphicon-chevron-right"></span></button>
							</div>
						</div>                           
					</form>     
				
				<h6 style="padding-top:10px" ><a href="/password/email">{{trans('auth.forgotPassword')}}</a></h6>
            </div>      
		
        <div>                     
	</div>
    
</body>
</html>
