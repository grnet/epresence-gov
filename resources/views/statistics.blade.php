@extends('static')

@section('head-extra')
	<script src="Highcharts-4.1.8/highcharts.js"></script>
    <script src="Highcharts-4.1.8/modules/exporting.js"></script>

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
		
		.chartbox {
			border:1px solid #F7F7F7; 
			padding:10px
		}
	
	</style>

    

    
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->    
    


<script type="text/javascript">
$(document).ready(function() {		
	
	
			
// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

	$('[data-toggle="tooltip"]').tooltip();

	
// CHART 01
	
 $('#Chart01').highcharts({
        chart: {
            type: 'areaspline'
        },
        title: {
            text: 'Average fruit consumption during one week'
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            verticalAlign: 'top',
            x: 150,
            y: 100,
            floating: true,
            borderWidth: 1,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        xAxis: {
            categories: [
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday',
                'Sunday'
            ],
            plotBands: [{ // visualize the weekend
                from: 4.5,
                to: 6.5,
                color: 'rgba(68, 170, 213, .2)'
            }]
        },
        yAxis: {
            title: {
                text: 'Fruit units'
            }
        },
        tooltip: {
            shared: true,
            valueSuffix: ' units'
        },
        credits: {
            enabled: false
        },
        plotOptions: {
            areaspline: {
                fillOpacity: 0.5
            }
        },
        series: [{
            name: 'John',
            data: [3, 4, 3, 5, 4, 10, 12]
        }, {
            name: 'Jane',
            data: [1, 3, 4, 3, 3, 5, 4]
        }]
    });	
	
	
	
// CHART 02

 $('#Chart02').highcharts({
        chart: {
            type: 'pie',
            options3d: {
                enabled: true,
                alpha: 45,
                beta: 0
            }
        },
        title: {
            text: 'Browser market shares at a specific website, 2014'
        },
        tooltip: {
            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                depth: 35,
                dataLabels: {
                    enabled: true,
                    format: '{point.name}'
                }
            }
        },
        series: [{
            type: 'pie',
            name: 'Browser share',
            data: [
                ['Firefox',   45.0],
                ['IE',       26.8],
                {
                    name: 'Chrome',
                    y: 12.8,
                    sliced: true,
                    selected: true
                },
                ['Safari',    8.5],
                ['Opera',     6.2],
                ['Others',   0.7]
            ]
        }]
    });	
		
	
// CHART 03
	
	
	 $('#Chart03').highcharts({
        chart: {
            type: 'column'
        },
        title: {
            text: ''
        },
        xAxis: {
            categories: [
                'Seattle HQ',
                'San Francisco',
                'Tokyo'
            ]
        },
        yAxis: [{
            min: 0,
            title: {
                text: 'Employees'
            }
        }, {
            title: {
                text: 'Profit (millions)'
            },
            opposite: true
        }],
        legend: {
            shadow: false
        },
        tooltip: {
            shared: true
        },
        plotOptions: {
            column: {
                grouping: false,
                shadow: false,
                borderWidth: 0
            }
        },
        series: [{
            name: 'Employees',
            color: 'rgba(165,170,217,1)',
            data: [150, 73, 20],
            pointPadding: 0.3,
            pointPlacement: -0.2
        }, {
            name: 'Employees Optimized',
            color: 'rgba(126,86,134,.9)',
            data: [140, 90, 40],
            pointPadding: 0.4,
            pointPlacement: -0.2
        }, {
            name: 'Profit',
            color: 'rgba(248,161,63,1)',
            data: [183.6, 178.8, 198.5],
            tooltip: {
                valuePrefix: '$',
                valueSuffix: ' M'
            },
            pointPadding: 0.3,
            pointPlacement: 0.2,
            yAxis: 1
        }, {
            name: 'Profit Optimized',
            color: 'rgba(186,60,61,.9)',
            data: [203.6, 198.8, 208.5],
            tooltip: {
                valuePrefix: '$',
                valueSuffix: ' M'
            },
            pointPadding: 0.4,
            pointPlacement: 0.2,
            yAxis: 1
        }]
    });
	
	
	
	
});
</script>
@endsection

@section('statistics-active')
class="active"
@endsection

@section('content')

<section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">
                
                       
<!-- Tab line -START -->
            <div class="row">
                <div class="col-sm-12">            
                    <ul class="nav nav-tabs">
                          <li class="active"><a href="#">Χρήση Σε Πραγματικό Χρόνο</a></li>
                          <li><a href="/statistics-all">Χρήση σε Χρονικές Περιόδους</a></li>
                    </ul>
                </div>
            </div>   
<!-- Tab line -END -->

			<div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-6">
                	<div id="Chart01" class="chartbox"></div>
                </div>
                <div class="col-md-6">
					<div id="Chart02" class="chartbox"></div>
                </div>
            </div>
            
            <div class="small-gap"></div>
            <div class="row">
            	<div class="col-md-12">
                	<div id="Chart03" class="chartbox"></div>
                </div>
            </div>
            
            </div><!--/.box-->
        </div><!--/.container-->        
    </section>
	
@endsection