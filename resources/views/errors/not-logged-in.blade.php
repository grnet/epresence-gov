@extends('app')
@section('extra-css')
<style>
		.container
			{
				min-width: 400px !important;
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
							{!!trans('errors.not_logged_in')!!}
							<div class="error-actions">
								<a href="/" class="btn btn-primary btn-lg"><span class="glyphicon glyphicon-home"></span>
								{{trans('errors.homePage')}} </a>
							</div>
						</div>
					</div>
				</div>
            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
@endsection
