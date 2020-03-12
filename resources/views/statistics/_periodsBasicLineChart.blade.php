title: {
            text: '{!!trans('statistics.noUsersPerMonth')!!}',
            x: 0
        },
        xAxis: {
			title: {
                text: '{!!trans('statistics.monthsCap')!!}'
            },
            categories: ['{!! implode("', '", $data['periods']) !!}']
        },
        yAxis: {
			allowDecimals: false,
            title: {
                text: '{!!trans('statistics.noParticipants')!!}'
            },
            plotLines: [{
                value: 0,
                width: 1,
				rotation: 45,
                color: '#808080'
            }]
			
        },
		tooltip:{
			valueSuffix:' {!!trans('statistics.participantsLow')!!}'
		},
        legend: {
            layout: 'vertical',
            align: 'right',
            verticalAlign: 'middle',
            borderWidth: 0
        },
        series: [{
            name: 'Desktop-Mobile',
            data: [{!! implode(", ", $data['users_no_desktop']) !!}]
        }]
