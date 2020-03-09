@extends('app-noMenu')

@section('header-javascript')
	<link href="select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
	<script type="text/javascript" src="/select2/select2_locale_el.js"></script>
    <link rel="stylesheet" href="/select2/select2-small.css">

	<!-- checkbox --> 
	<script src="bootstrap-checkbox-x/checkbox-x.js" type="text/javascript"></script>
	<link rel="stylesheet" href="bootstrap-checkbox-x/checkbox-x.css">

	<!-- bootstrap text editor       -->
    <link href="/summernote/summernote.css" rel="stylesheet">
    <script src="/summernote/summernote.min.js"></script>
	<script src="/summernote/summernote-el-GR.js"></script>

	<link rel="stylesheet" href="/css/font-awesome.css">    

        
    <link href="/css/main.css" rel="stylesheet">
	<link href="/css/eDatatables.css" rel="stylesheet">
	
	<script type="text/javascript" src="/datatables/jquery.dataTables.js"></script>
    <script type="text/javascript" src="/datatables/dataTables.bootstrap.js"></script>
	<link href="/datatables/dataTables.bootstrap.css" rel="stylesheet">

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


   	<script type="text/javascript">
		$(document).ready(function() {		
		
		
  		  		
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

  		$('[data-toggle="tooltip"]').tooltip();



// ΣΥΝΑΡΤΗΣΕΙΣ ΣΧΕΤΙΚΕΣ ΜΕ ΤΗΝ ΑΠΟΣΤΟΛΗ EMAIL

			$('.summernote').summernote({
				lang: 'el-GR' 
			});
		
	  		$( "#InvEmail" ).click(function() {
				$("#InvEmailMessage").hide();						
  				$("#InvEmailModal").modal("show");
			});
	
			$("#InvEmailSubmitBtn").click(function() {						
				// αποστολή email...και μετά από ελέγχους αποστολής το ανάλογο μύνημα
				 $("#InvEmailMessage").text("{{trans('errors.emailSent')}}");
				 $("#InvEmailMessage").removeClass( "alert-danger" ).addClass( "alert-info" );
				 $("#InvEmailMessage").show();
				   window.setTimeout(function () {
					 $("#InvEmailModal").modal("hide");
					}, 500);
			});	
			
			
			
        } );
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
		.error-template {padding: 40px 15px;text-align: center;}
		.error-actions {margin-top:15px;margin-bottom:15px;}
		.error-actions .btn { margin-right:10px; }

	
	</style>
@endsection

@section('content')
	<section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px"> 
                       
				<div class="row">
					<div class="col-md-12">
						<div class="error-template">
							<h1>{!!trans('errors.downMaintenance')!!}</h1>
							<div class="error-details">
								{!!trans('errors.epresenceDownMaintenance')!!}
							</div>
							<div class="error-actions">
								<a href="mailto:{{env('SUPPORT_MAIL')}}" class="btn btn-default btn-lg"><span class="glyphicon glyphicon-envelope"></span> {{trans('errors.supportContact')}} </a>
							</div>
						</div>
					</div>
				</div>
  
            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
