chart: {
            type: 'column'
        },
        title: {
            text: '{!!trans('statistics.incrDecr')!!}'
        },
        xAxis: {
        categories: {!! json_encode($periods)!!}
        },
        credits: {
            enabled: false
        },
		yAxis: {
            title: {
                text: '{!!trans('statistics.noIncrDecr')!!} %'
            },
            plotLines: [{
                value: 0,
                width: 1,
				rotation: 45,
                color: '#808080'
            }]
			
        },
		tooltip: {
				pointFormat: '{series.name}: <b>{point.y}</b> ({point.conf} {!!trans('statistics.conferences')!!})<br/>',
                valueSuffix: ' %'
        },
        series: [{
            name: '{!!trans('statistics.conferences')!!}',
                data: [
                    @foreach($data['all'] as $stat)
                        {
                         y: {!!$stat['percentage']  !!},
                        conf: {!!$stat['count']  !!}
                        },
                    @endforeach
                ],
        }, {
            name: '{!!trans('statistics.electoralCap')!!}',
            data:  [
            @foreach($data['elector'] as $stat)
             {
             y:{!!$stat['percentage'] !!},
             conf:{!!$stat['count'] !!}
               },
            @endforeach
                ]
        }, {
            name: '{!!trans('statistics.doctoralCap')!!}',
            data: [
            @foreach($data['post_graduate'] as $stat)
             {
                 y:{!!$stat['percentage'] !!},
                 conf:{!!$stat['count'] !!}
                 },
            @endforeach
            ],
        }, {
            name: '{!!trans('statistics.otherMeetingsCap')!!}',
            data: [
            @foreach($data['other'] as $stat)
                 {
             y:{!!$stat['percentage'] !!},
             conf:{!!$stat['count'] !!}
                 },
            @endforeach
            ],
        }, {
            name: '{!!trans('statistics.testConfsCap')!!}',
            data: [
            @foreach($data['test'] as $stat)
             {
            y:{!!$stat['percentage'] !!},
             conf:{!!$stat['count'] !!}
             },
            @endforeach
            ],
        }]
