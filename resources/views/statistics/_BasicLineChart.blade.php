title: {
text: '{!!trans('statistics.noConferences')!!}',
x: -20 //center
},
xAxis: {
title: {
text: '{!!trans('statistics.monthsCap')!!}'
},
categories: {!! json_encode($periods)!!}
},
yAxis: {
title: {
text: '{!!trans('statistics.noConferences')!!}'
},
plotLines: [{
value: 0,
width: 1,
rotation: 45,
color: '#808080'
}]

},
legend: {
layout: 'vertical',
align: 'right',
verticalAlign: 'middle',
borderWidth: 0
},
series: [{
name: '{!!trans('statistics.conferences')!!}',
data: {!! json_encode($data['all']) !!},
tooltip: {
valueSuffix: ' {!!trans('statistics.confs')!!}'
}
}, {
name: '{!!trans('statistics.electoralCap')!!}',
data: {!! json_encode($data['elector']) !!},
tooltip: {
valueSuffix: ' {!!trans('statistics.electoral')!!}'
}
}, {
name: '{!!trans('statistics.doctoralCap')!!}',
data: {!! json_encode($data['post_graduate']) !!},
tooltip: {
valueSuffix: ' {!!trans('statistics.doctoral')!!}'
}
}, {
name: '{!!trans('statistics.otherMeetingsCap')!!}',
data: {!! json_encode($data['other']) !!},
tooltip: {
valueSuffix: ' {!!trans('statistics.otherMeetings')!!}'
}
}, {
name: '{!!trans('statistics.testConfsCap')!!}',
data: {!! json_encode($data['test']) !!},
tooltip: {
valueSuffix: ' {!!trans('statistics.testConfs')!!}'
}
}]