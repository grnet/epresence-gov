chart: {
            type: 'bar'
        },
        title: {
            text: '{!! $data['graph_title'] !!}'
        },
        xAxis: {
            categories: ['{!! implode("', '", $data['periods']) !!}']
        },
        yAxis: {
            min: 0,
			allowDecimals: false,
            title: {
                text: '{!!trans('statistics.noConferences')!!}'
            }
        },
		tooltip: {
            valueSuffix: ' {!!trans('statistics.confs')!!}'
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
                stacking: 'normal'
            }
        },
        series: [{
            name: '{!! $data['conferences_one']['title'] !!}',
            data: [{!! implode(", ", array_except($data['conferences_one'], ['title'])) !!}]
        }, {
            name: '{!! $data['conferences_two']['title'] !!}',
            data: [{!! implode(", ", array_except($data['conferences_two'], ['title'])) !!}]
        }, {
            name: '{!! $data['conferences_three']['title'] !!}',
            data: [{!! implode(", ", array_except($data['conferences_three'], ['title'])) !!}]
        }, {
            name: '{!! $data['conferences_four']['title'] !!}',
            data: [{!! implode(", ", array_except($data['conferences_four'], ['title'])) !!}]
        }]
