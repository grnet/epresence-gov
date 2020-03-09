@extends('static')
@section('head-extra')
    <style>
		#main-slider {
		  background-image: url(images/slider-index.jpg);
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
        .counter-small-2{
            font-size:18px;
            color: #fff;
            padding-top:10px;
        }
		p{font-size: 1.11em;}
	</style>
    @if(Auth::check())
        <script>
            $(document).ready(function() {
                setInterval(function(){
                    $.get("/update_front_stats")
                            .done(function (data) {
                                obj = JSON.parse(data);
                                    $("#1_1").html(obj.total_total_conferences);
                                    $("#1_2").html(obj.total_desktop_mobile);
                                    $("#1_3").html(obj.total_h323);
                                    $("#2_1").html(obj.today_total_conferences);
                                    $("#2_2").html(obj.today_desktop_mobile);
                                    $("#2_3").html(obj.today_h323);
                                    $("#3_1").html(obj.now_total_conferences);
                                    $("#3_2").html(obj.now_desktop_mobile);
                                    $("#3_3").html(obj.now_h323);
                                console.log('Front page statistics updated');
                            })
                            .fail(function (xhr, textStatus, errorThrown) {
                                console.log('The statistics failed to refresh!');
                                console.log(xhr.responseText);
                            });
                }, 60000);
            });
        </script>
     @endif
@endsection

@section('home-active')
class="active"
@endsection

@section('content')
    <!--/#main-slider-->
        <section id="main-slider" class="carousel">
        <div class="carousel-inner">
            <div class="item active">
                <div class="container">
                    <div class="carousel-content">
                        <h1>e:Presence</h1>
                        <p class="lead carousel-shadow">{!!trans('site.epresenceTitle')!!}</p>
                    </div>
                </div>
            </div><!--/.item-->
            <div class="item">
                <div class="container">
                    <div class="carousel-content">
                        <h1>{{trans('site.easyAccess')}}</h1>
                        <p class="lead carousel-shadow">{{trans('site.easyAccessText')}}</p>
                    </div>
                </div>
            </div><!--/.item-->
            <div class="item">
                <div class="container">
                    <div class="carousel-content">
                        <h1>{{trans('site.highQuality')}}</h1>
                        <p class="lead carousel-shadow">{{trans('site.highQualityText')}}</p>
                    </div>
                </div>
            </div><!--/.item-->
        </div><!--/.carousel-inner-->
        <a class="prev" href="#main-slider" data-slide="prev"><i class="fa fa-chevron-left"></i></a>
        <a class="next" href="#main-slider" data-slide="next"><i class="fa fa-chevron-right"></i></a>
    </section><!--/#main-slider-->
    
    

	<section id="Index">
		<div class="container">
			<div class="box first" style="padding: 30px 50px">
				<div class="row">
					<h3 style="color:#52B6EC">{{trans('site.serviceDescription')}}</h3>
					<hr>
					@if (session('status'))
							<div class="alert alert-success">
								{{ session('status') }}
							</div>
					@endif
					<p class="lead">{!!trans('site.serviceText1')!!}</p>
					<div class="row">
						<div class="col-md-6">
							<div>
								<iframe width="100%" height="300px" src="https://www.youtube.com/embed/Vcc9dN6ptTE?rel=0&amp;showinfo=0" frameborder="0" allowfullscreen></iframe>
							</div>
						</div>
						<div class="col-md-6">
							{!!trans('site.serviceText2')!!}
						</div>
					</div>
				</div>
				<div class="small-gap"></div>
				<p class="small">{{trans('site.serviceLegal')}}</p>
			</div>
		</div><!--/.row-->
    </section>
     <section id="counter">
        <div class="container">
            <div class="box " style="background: #555 none repeat scroll 0% 0%; padding-top:20px; padding-bottom:5px">
                <div class="center">
                
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2"><div class="icon-lg icon-usage"><p class="font-counter counter-small">{{trans('site.serviceUsage')}}</p></div></div>
                        <div class="col-sm-2"><div class="icon-lg icon-teleconf" data-toggle="tooltip" data-placement="top" title="{{trans('site.noConferences')}}"><i class="fa fa-comment "></i></div></div>
                        <div class="col-sm-2"><div class="icon-lg icon-user" data-toggle="tooltip" data-placement="top" title="{{trans('site.desktopUsers')}}"><i class="fa fa-user "></i></div></div>
                        <div class="col-sm-2"><div class="icon-lg icon-terminal"  data-toggle="tooltip" data-placement="top" title="{{trans('site.roomUsers')}}"><i class="fa fa-desktop "></i></div></div>
                        <div class="col-sm-2"></div>
                    </div>
                    
                 	<div class="row">          
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2"><p class="font-counter counter-small">{{trans('site.total')}}:</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="1_1" >{{ DB::table('service_usage')->where('option','total')->value('total_conferences') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="1_2" >{{ DB::table('service_usage')->where('option','total')->value('desktop_mobile') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="1_3" >{{ DB::table('service_usage')->where('option','total')->value('h323') }}</p></div>
                        <div class="col-sm-2"></div>
 					</div>
                    
                 	<div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2"><p class="font-counter counter-small">{{trans('site.today')}}:</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="2_1" >{{ DB::table('service_usage')->where('option','today')->value('total_conferences') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="2_2" >{{ DB::table('service_usage')->where('option','today')->value('desktop_mobile') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="2_3" >{{ DB::table('service_usage')->where('option','today')->value('h323') }}</p></div>
                        <div class="col-sm-2"></div>
 					</div>
                    
                 	<div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-2"><p class="font-counter counter-small">{{trans('site.now')}}:</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="3_1" >{{ DB::table('service_usage')->where('option','now')->value('total_conferences') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="3_2" >{{ DB::table('service_usage')->where('option','now')->value('desktop_mobile') }}</p></div>
                        <div class="col-sm-2"><p class="font-counter" id="3_3" >{{ DB::table('service_usage')->where('option','now')->value('h323') }}</p></div>
                        <div class="col-sm-2"></div>
               		</div>
                </div><!--/.center-->
                <div class="center">
                <p class="font-counter counter-small-2">{{trans('site.moneySavedText')}} {{number_format(round(DB::table('service_usage')->where('option','total')->value('euro_saved'),-5),0,'.','.')}}€.</p>
                </div>
                </div><!--/.box-->
        </div><!--/.container-->
    </section><!--/.counter-->



@endsection
