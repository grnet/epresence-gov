<script type="text/javascript">
    $(document).ready(function() {

        $("#FieldPeriod").select2({
            allowClear: false
        });

        $("#RefreshPage").click(function() {
            location.reload(true);
        });

// DATE FIELD

        $('.datepicker').datepicker({
            format: "dd/mm/yy",
            todayBtn: "linked",
            language: "el",
            autoclose: true,
            todayHighlight: true
        });

// ΕΝΕΡΓΟΠΟΙΗΣΗ TOOLTIP

        $('[data-toggle="tooltip"]').tooltip();
        $('#Chart01').highcharts({
                chart: {
                    type: 'bar'
                },
                title: {
                    text: '{!!trans('statistics.noUsersPerRoom')!!}'
                },
                xAxis: {
                    min: 0,
                    title: {
                        text: '{!!trans('statistics.rooms')!!}'
                    },
                    categories: [
                            @foreach($statistics['realtime_users_per_room']['conference_info'] as $conference_info)
                        [{{$conference_info[0]}},{!!json_encode($conference_info[1])!!}],
                        @endforeach
                    ],
                    reversed: true,
                    labels: {
                        format: '{value.0}'
                    }
                },
                yAxis: {
                    allowDecimals: false,
                    title: {
                        text: '{!!trans('statistics.noParticipants')!!}'
                    }
                },
                tooltip: {
                    headerFormat: '{point.y} {!!trans('statistics.users')!!} {point.key.1}',
                    pointFormat: '<td></td>',
                    split: true,
                    useHTML: true
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
                    name: 'Desktop-Mobile',
                    data: [{{implode(',' ,$statistics['realtime_users_per_room']['users_no_desktop'])}}],
                    index:1,
                    cursor:"pointer",
                    point:{
                        events:{
                            click:function(e) {
                                window.location.href = "/conferences/"+e.point.category[0]+"/manage";
                            }
                        }
                    }
                }]

            },
            // Add some life
            function (chart) {
                if (!chart.renderer.forExport) {
                    function requestData() {
                        $.ajax({
                            dataType: "json",
                            url: '/statistics/realtime/users_per_room',
                            success: function(data) {
                                var a = data['users_no_desktop'];
                                var desktop = a.map(function (x) {
                                    return parseInt(x);
                                });
                                chart.xAxis[0].setCategories(data['conference_info'], true, true);
                                chart.series[1].setData(desktop, true, true);
                            },
                            cache: false
                        });
                    }

                    setInterval(requestData, 30000);
                }
            });

        // CHART: Αριθμός συμμετεχόντων σε κάθε δωμάτιο

        $('#Chart02').highcharts({
                chart: {
                    type: 'gauge',
                    plotBackgroundColor: null,
                    plotBackgroundImage: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: '{!!trans('statistics.noBusyRooms')!!}'
                },
                pane: {
                    startAngle: -150,
                    endAngle: 150,
                    background: [{
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#FFF'],
                                [1, '#333']
                            ]
                        },
                        borderWidth: 0,
                        outerRadius: '109%'
                    }, {
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#333'],
                                [1, '#FFF']
                            ]
                        },
                        borderWidth: 1,
                        outerRadius: '107%'
                    }, {
                        // default background
                    }, {
                        backgroundColor: '#DDD',
                        borderWidth: 0,
                        outerRadius: '105%',
                        innerRadius: '103%'
                    }]
                },
                // the value axis
                yAxis: {
                    min: 0,
                    max: 150,
                    minorTickInterval: 'auto',
                    minorTickWidth: 1,
                    minorTickLength: 10,
                    minorTickPosition: 'inside',
                    minorTickColor: '#666',
                    tickPixelInterval: 30,
                    tickWidth: 2,
                    tickPosition: 'inside',
                    tickLength: 10,
                    tickColor: '#666',
                    labels: {
                        step: 3,
                        rotation: 'auto'
                    },
                    title: {
                        text: '{!!trans('statistics.rooms')!!}'
                    },
                    plotBands: [{
                        from: 0,
                        to: 80,
                        color: '#55BF3B' // green
                    }, {
                        from: 80,
                        to: 120,
                        color: '#DDDF0D' // yellow
                    }, {
                        from: 120,
                        to: 150,
                        color: '#DF5353' // red
                    }]
                },
                series: [{
                    name: '{!!trans('statistics.busy')!!}',
                    data: [{{$statistics['realtime_num_of']['conferences']}}],
                    tooltip: {
                        valueSuffix: ' {!!trans('statistics.rooms')!!}'
                    }
                }]
            },
            // Add some life
            function (chart) {
                if (!chart.renderer.forExport) {
                    function requestData() {
                        $.ajax({
                            url: '/statistics/realtime/conferences',
                            success: function(data) {
                                var point = chart.series[0].points[0],
                                    newVal = parseInt(data);

                                point.update(newVal);

                            },
                            cache: false
                        });
                    }

                    setInterval(requestData, 30000);
                }
            });

        $('#Chart03').highcharts({
                chart: {
                    type: 'gauge',
                    plotBackgroundColor: null,
                    plotBackgroundImage: null,
                    plotBorderWidth: 0,
                    plotShadow: false
                },
                title: {
                    text: '{!!trans('statistics.noDesktopUsers')!!}'
                },
                pane: {
                    startAngle: -150,
                    endAngle: 150,
                    background: [{
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#FFF'],
                                [1, '#333']
                            ]
                        },
                        borderWidth: 0,
                        outerRadius: '109%'
                    }, {
                        backgroundColor: {
                            linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1 },
                            stops: [
                                [0, '#333'],
                                [1, '#FFF']
                            ]
                        },
                        borderWidth: 1,
                        outerRadius: '107%'
                    }, {
                        // default background
                    }, {
                        backgroundColor: '#DDD',
                        borderWidth: 0,
                        outerRadius: '105%',
                        innerRadius: '103%'
                    }]
                },
                // the value axis
                yAxis: {
                    min: 0,
                    max: 250,
                    minorTickInterval: 'auto',
                    minorTickWidth: 1,
                    minorTickLength: 10,
                    minorTickPosition: 'inside',
                    minorTickColor: '#666',
                    tickPixelInterval: 30,
                    tickWidth: 2,
                    tickPosition: 'inside',
                    tickLength: 10,
                    tickColor: '#666',
                    labels: {
                        step: 3,
                        rotation: 'auto'
                    },
                    title: {
                        text: 'Desktop-Mobile'
                    },
                    plotBands: [{
                        from: 0,
                        to: 120,
                        color: '#55BF3B' // green
                    }, {
                        from: 120,
                        to: 200,
                        color: '#DDDF0D' // yellow
                    }, {
                        from: 200,
                        to: 250,
                        color: '#DF5353' // red
                    }]
                },
                series: [{
                    name: 'Desktop-Mobile',
                    data: [{{$statistics['realtime_num_of']['users_no_desktop']}}],
                    tooltip: {
                        valueSuffix: ' {!!trans('statistics.users')!!}'
                    }
                }]
            },
            // Add some life
            // Add some life
            function (chart) {
                if (!chart.renderer.forExport) {
                    function requestData() {
                        $.ajax({
                            url: '/statistics/realtime/users_no_desktop',
                            success: function(data) {
                                var point = chart.series[0].points[0],
                                    newVal = parseInt(data);

                                point.update(newVal);

                            },
                            cache: false
                        });
                    }

                    setInterval(requestData, 30000);
                }
            });

        //CHART: Αριθμός συμμετεχόντων σε κάθε δωμάτιο

        $('#Chart06').highcharts({
                title: {
                    text: '{!!trans('statistics.todayConnections')!!}',
                    x: -20 //center
                },
                xAxis: {
                    title: {
                        text: '{!!trans('statistics.time')!!}'
                    },
                    labels: {
                        rotation: 45
                    },
                    type: 'datetime',
                    dateTimeLabelFormats: {
                        hour: '%H:%M'
                    }
                },
                yAxis: {
                    allowDecimals: false,
                    title: {
                        text: '{!!trans('statistics.noUsers')!!}'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    formatter: function () {
                        var identifier = this.series.name === '{{trans('statistics.conferences')}}' ? ' {{trans('statistics.conferences')}}' : ' {!!trans('statistics.users')!!}';
                        return '<b>' + this.series.name + '</b><br/>' +
                            Highcharts.dateFormat('%d-%m-%Y %H:%M', this.x) + '<br/>' +
                            Highcharts.numberFormat(this.y, 0) + identifier;
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'middle',
                    borderWidth: 0
                },
                series: [
                    {
                        name: '{{trans('statistics.conferences')}}',
                        data: [{{implode(",", $statistics["realtime_daily"]["conferences_no"])}}],
                        pointStart: Date.UTC({{$statistics['realtime_daily']['year']}},{{$statistics['realtime_daily']['month']}},{{$statistics['realtime_daily']['day']}}, 0, 5),
                        pointInterval: 300 * 1000,// one day,
                        color:'red'
                    },
                    {
                    name: 'Desktop-Mobile',
                    data: [{{implode(",", $statistics["realtime_daily"]["users_no_desktop"])}}],
                    pointStart: Date.UTC({{$statistics['realtime_daily']['year']}},{{$statistics['realtime_daily']['month']}},{{$statistics['realtime_daily']['day']}}, 0, 5),
                    pointInterval: 300 * 1000 // one day
                },
                    @if(Auth::user()->hasRole('SuperAdmin'))
                     {
                        name: "Desktop-Mobile Distinct",
                        data: [{{implode(",", $statistics["realtime_daily"]["distinct_users_no_desktop"])}}],
                        pointStart: Date.UTC({{$statistics["realtime_daily"]["year"]}},{{$statistics["realtime_daily"]["month"]}},{{$statistics["realtime_daily"]["day"]}}, 0, 5),
                        pointInterval: 300 * 1000
                    }
                    @endif
                ]
            },
            // Add some life
            function (chart) {
                if (!chart.renderer.forExport) {
                    function requestData() {
                        $.ajax({
                            dataType: "json",
                            url: '/statistics/realtime/users_daily',
                            success: function(data) {
                                var conferences_no = parseInt(data['conferences_no']);
                                var users_no_desktop = parseInt(data['users_no_desktop']);
                                var users_no_h323 = parseInt(data['users_no_h323']);
                                chart.series[0].addPoint(conferences_no);
                                chart.series[1].addPoint(users_no_desktop);

                                @if(Auth::user()->hasRole('SuperAdmin'))
                                var distinct_users_no_desktop = parseInt(data['distinct_users_no_desktop']);
                                chart.series[2].addPoint(distinct_users_no_desktop);
                                @endif
                            },
                            cache: false
                        });
                    }
                    function startTimer() {
                      requestData();
                      setInterval(requestData, 300000);
                    }
                    setTimeout(startTimer, {{$statistics['sec_till_five_from_now']}});
                }
            });
    });
</script>