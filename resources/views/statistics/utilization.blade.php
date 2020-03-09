@extends('app')

@section('header-javascript')
    <!-- bootstrap date-picker    -->
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.js"></script>
    <script type="text/javascript" src="/bootstrap-datepicker-master/bootstrap-datepicker.el.js"></script>
    <link href="/bootstrap-datepicker-master/datepicker3.css" rel="stylesheet">

    <!-- select-2    -->
    <link href="/select2/select2.css" rel="stylesheet">
    <script type="text/javascript" src="/select2/select2.js"></script>
    <script type="text/javascript" src="/select2/select2_locale_el.js"></script>

    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/drilldown.js"></script>
    <script src="https://code.highcharts.com/modules/solid-gauge.js"></script>

    <style>
        .container {
            min-width: 400px !important;
        }

        .noshadow {
            -webkit-box-shadow: none;
            -moz-box-shadow: none;
            box-shadow: none;
            border: 0px;
        }

        .chartbox {
            border: 1px solid #F7F7F7;
        }

        .datepicker {
            padding: 0px;
        }

        .formDiv {
            float: left;
            margin-left: 10px;
        }
    </style>
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
@endsection
@section('extra-css')

@endsection

@section('statistics-active')
    class="active"
@endsection

@section('content')

    <section id="Users">
        <div class="container">
            <div class="box first" style="margin-top:100px">

                <div class="row">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            <li><a href="/statistics">{!!trans('statistics.realTimeUsage')!!}</a></li>
                            <li><a href="/statistics/periods">{!!trans('statistics.pastPeriodUsage')!!}</a></li>
                            <li><a href="#">{!!trans('statistics.personalized')!!}</a></li>
                            @if(Auth::user()->hasRole('SuperAdmin'))
                                <li><a href="/statistics/report">{!!trans('statistics.usageReports')!!}</a></li>
                                <li><a href="/statistics/demo-room">Demo Room</a></li>
                                <li class="active"><a href="/statistics/utilization">Utilization</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="well well-sm" style="margin-top:50px;">
                    @foreach($statistics_results as $year=>$year_stats)
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Στατιστικά χρήσης (zoom) {{$year}}</h3>
                                <table style="margin-top:40px; width:100%" cellpadding="0" cellspacing="0" border="0"
                                       class="table table-hover table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <th>{{get_month_locale(Carbon\Carbon::parse($month->month)->month)}}</th>
                                        @endforeach
                                        <th>{{$year}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><strong>Τηλεδιασκέψεις</strong></td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστος αριθμός</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_conferences}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_conferences']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_conferences_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_conferences_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστες ταυτόχρονες</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_concurrent_conferences}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_concurrent_conferences']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_concurrent_conferences_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_concurrent_conferences_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- μέσος όρος ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_conferences}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_conferences']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Συνδέσεις Desktop-Mobile</strong></td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστος αριθμός</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_dm_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_dm_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_dm_connections_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_dm_connections_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστες ταυτόχρονες</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_concurrent_dm}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_concurrent_dm']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_concurrent_dm_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_concurrent_dm_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- μέσος όρος ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_dm_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_dm_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Συνδέσεις H.323</strong></td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστος αριθμός</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_h323_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_h323_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_h323_connections_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_h323_connections_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστες ταυτόχρονες</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_concurrent_h323}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_concurrent_h323']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_concurrent_h323_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_concurrent_h323_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- μέσος όρος ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_h323_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_h323_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Χρήση χωρητικότητας (zoom hosts) συνδέσεων (9:00-17:00)</strong></td>
                                    </tr>
                                    <tr>
                                        <td>έως 20%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->host_cap_0}}</td>
                                        @endforeach
                                        <td>{{$year_stats['host_cap_0']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 40%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->host_cap_20}}</td>
                                        @endforeach
                                        <td>{{$year_stats['host_cap_20']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 60%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->host_cap_40}}</td>
                                        @endforeach
                                        <td>{{$year_stats['host_cap_40']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 80%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->host_cap_60}}</td>
                                        @endforeach
                                        <td>{{$year_stats['host_cap_60']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 100%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->host_cap_80}}</td>
                                        @endforeach
                                        <td>{{$year_stats['host_cap_80']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Χρήση χωρητικότητας H.323 συνδέσεων (9:00-17:00)</strong></td>
                                    </tr>
                                    <tr>
                                        <td>έως 20%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_0}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_0']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 40%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_20}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_20']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 60%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_40}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_40']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 80%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_60}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_60']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 100%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_80}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_80']}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="well well-sm" style="margin-top:50px;">
                    @foreach($former_statistics_results as $year=>$year_stats)
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Στατιστικά χρήσης {{$year}}</h3>
                                <table style="margin-top:40px; width:100%" cellpadding="0" cellspacing="0" border="0"
                                       class="table table-hover table-striped table-bordered">
                                    <thead>
                                    <tr>
                                        <th></th>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <th>{{get_month_locale(Carbon\Carbon::parse($month->month)->month)}}</th>
                                        @endforeach
                                        <th>{{$year}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>Τηλεδιασκέψεις ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_conferences}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_conferences']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Συνδέσεις Desktop-Mobile</strong></td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστος αριθμός</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_dm_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_dm_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_dm_connections_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_dm_connections_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστες ταυτόχρονες</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_concurrent_dm}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_concurrent_dm']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_concurrent_dm_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_concurrent_dm_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- μέσος όρος ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_dm_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_dm_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Συνδέσεις H.323</strong></td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστος αριθμός</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_h323_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_h323_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_h323_connections_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_h323_connections_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- Μέγιστες ταυτόχρονες</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->max_concurrent_h323}}</td>
                                        @endforeach
                                        <td>{{$year_stats['max_concurrent_h323']}}</td>
                                    </tr>
                                    <tr>
                                        <td>- την ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{Carbon\Carbon::parse($month->max_concurrent_h323_day)->format('d/m')}}</td>
                                        @endforeach
                                        <td>{{Carbon\Carbon::parse($year_stats['max_concurrent_h323_day'])->format('d/m/y')}}</td>
                                    </tr>
                                    <tr>
                                        <td>- μέσος όρος ανά ημέρα</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->average_h323_connections}}</td>
                                        @endforeach
                                        <td>{{$year_stats['average_h323_connections']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Χρήση χωρητικότητας Desktop-mobile συνδέσεων (9:00-17:00)</strong></td>
                                    </tr>
                                    <tr>
                                        <td>έως 20%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->dm_cap_0}}</td>
                                        @endforeach
                                        <td>{{$year_stats['dm_cap_0']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 40%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->dm_cap_20}}</td>
                                        @endforeach
                                        <td>{{$year_stats['dm_cap_20']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 60%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->dm_cap_40}}</td>
                                        @endforeach
                                        <td>{{$year_stats['dm_cap_40']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 80%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->dm_cap_60}}</td>
                                        @endforeach
                                        <td>{{$year_stats['dm_cap_60']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 100%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->dm_cap_80}}</td>
                                        @endforeach
                                        <td>{{$year_stats['dm_cap_80']}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Χρήση χωρητικότητας H.323 συνδέσεων (9:00-17:00)</strong></td>
                                    </tr>
                                    <tr>
                                        <td>έως 20%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_0}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_0']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 40%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_20}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_20']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 60%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_40}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_40']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 80%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_60}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_60']}}</td>
                                    </tr>
                                    <tr>
                                        <td>έως 100%</td>
                                        @foreach($year_stats['month_statistics'] as $month)
                                            <td>{{$month->h323_cap_80}}</td>
                                        @endforeach
                                        <td>{{$year_stats['h323_cap_80']}}</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection
