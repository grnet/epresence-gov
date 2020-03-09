chart: {
zoomType: 'xy'
},
title: {
text: '{!! $graph_title !!}'
},
xAxis: [{
categories: ['{!! implode("', '", array_except($data['periods'], [0])) !!}'],
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
text: '{!! $spline_title !!}',
style: {
color: Highcharts.getOptions().colors[1]
}
}
}, { // Secondary yAxis
title: {
text: '{!! $column_title !!}',
style: {
color: Highcharts.getOptions().colors[0]
}
},
labels: {
format: '{value} {!! $column_valueSuffix !!}',
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
name: '{!! $column_title !!}',
type: 'column',
yAxis: 1,
data: [{!! implode(", ", $data['percentage']) !!}],
color: '#{!! $color !!}',
tooltip: {
valueSuffix: '{!! $column_valueSuffix !!}'
}

}, {
name: '{!! $spline_title !!}',
type: 'spline',
data: [{!! implode(", ", array_except($data['numbers'], [0])) !!}],
color: '#000000',
tooltip: {
valueSuffix: '{!! $spline_valueSuffix !!}'
}
}]