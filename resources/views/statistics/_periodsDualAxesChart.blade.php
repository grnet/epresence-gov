chart: {
            zoomType: 'xy'
        },
        title: {
            text: '{!!trans('statistics.noAndDuration')!!}'
        },
        xAxis: [{
            categories: ['{!! implode("', '", $data['periods']) !!}'],
            crosshair: true
        }],
        yAxis: [{ // Primary yAxis
			allowDecimals: false,
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            },
            title: {
                text: '{!!trans('statistics.durationConfMinutes')!!}',
                style: {
                    color: Highcharts.getOptions().colors[1]
                }
            }
        }, { // Secondary yAxis
            title: {
                text: '{!!trans('statistics.conferences')!!}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            labels: {
                format: '{value}',
                style: {
                    color: Highcharts.getOptions().colors[0]
                }
            },
            opposite: true
        }],
        tooltip: {
            shared: true
        },
        legend: {
            layout: 'vertical',
            align: 'left',
            x: 120,
            verticalAlign: 'top',
            y: 100,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
        series: [{
            name: '{!!trans('statistics.conferences')!!}',
            type: 'column',
            yAxis: 1,
            data: [{!! implode(", ", $data['conferences_no']) !!}],
            tooltip: {
                valueSuffix: ''
            }

        }, {
            name: '{!!trans('statistics.durationMinutes')!!}',
            type: 'spline',
            data: [{!! implode(", ", $data['conferences_duration']) !!}],
            tooltip: {
                valueSuffix: ' min'
            }
        }]
