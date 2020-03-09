chart: {
            type: 'column'
        },
        title: {
            text: '{!! $graph_title !!}'
        },
        subtitle: {
            text: ''
        },
        xAxis: {
			allowDecimals: false,
            type: 'category'
        },
        yAxis: {
			allowDecimals: false,
            title: {
                text: '{!!trans('statistics.totalPercent')!!}'
            }

        },
        legend: {
            enabled: false
        },
        plotOptions: {
            series: {
                borderWidth: 0,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.1f}%'
                }
            }
        },
        series: [{
            name: '{!!trans('statistics.institution')!!}',
            colorByPoint: true,
            data: [{{!! implode("}, {", $data['institutions']) !!}}]
		}],
		drilldown: {
            series: [{{!! implode("}, {", $data['institutions_departments']) !!}}
				]}, 		
		tooltip: { 
			headerFormat: '<span style="font-size:11px">{series.name}</span><br>', 
			pointFormat: '<span style="color:{point.color}">{point.name} {point.conf}</span>: <b>{point.y:.2f}%</b> of total<br/>' 
		}
