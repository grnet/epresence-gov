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
            <div class="box first" style="margin-top:100px;">

                <div class="row" style="padding-bottom:30px;">
                    <div class="col-sm-12">
                        <ul class="nav nav-tabs">
                            <li><a href="/statistics">{{trans('statistics.realTimeUsage')}}</a></li>
                            <li><a href="/statistics/periods">{{trans('statistics.pastPeriodUsage')}}</a></li>
                            <li><a href="/statistics/personalized">{!!trans('statistics.personalized')!!}</a></li>
                            @if(Auth::user()->hasRole('SuperAdmin'))
                                <li><a href="/statistics/report">{!!trans('statistics.usageReports')!!}</a></li>
                                <li class="active"><a href="/statistics/demo-room">Demo Room</a></li>
                                <li><a href="/statistics/utilization">Utilization</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><h4>10 πρώτοι συντονιστές σε συνδέσεις τον τελευταίο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Όνοματεπώνυμο</th>
                                <th>Email</th>
                                <th>Συνδέσεις</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($top_ten_coordinators_last_month as $user)
                                <tr>
                                    <td>{{$user->firstname}} {{$user->lastname}}
                                    </td>
                                    <td>{{$user->email}}
                                    </td>
                                    <td>{{$user->last_month_connections}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><h4>10 πρώτοι συντονιστές σε συνδέσεις από 1η Σεπτεμβρίου 2016 μέχρι τον προηγούμενο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Όνοματεπώνυμο</th>
                                <th>Email</th>
                                <th>Συνδέσεις</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($top_ten_coordinators as $user)
                                <tr>
                                    <td>{{$user->firstname}} {{$user->lastname}}
                                    </td>
                                    <td>{{$user->email}}
                                    </td>
                                    <td>{{$user->total_connections}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12"><h4>10 πρώτα ιδρύματα σε συνδέσεις τον τελευταίο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Οργανισμός</th>
                                <th>Συνδέσεις</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($top_ten_institutions_last_month as $institution)
                                <tr>
                                    <td>{{$institution['title']}}
                                    </td>
                                    <td>{{$institution['last_month_connections_count']}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><h4>10 πρώτα ιδρύματα σε συνδέσεις από 1η Σεπτεμβρίου 2016 μέχρι τον προηγούμενο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Οργανισμός</th>
                                <th>Συνδέσεις</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($top_ten_institutions as $institution)
                                <tr>
                                    <td>{{$institution['title']}}
                                    </td>
                                    <td>{{$institution['total_connections_count']}}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12"><h4>Συνολικές συνδέσεις ανά ώρα από 1η Σεπτεμβρίου 2016 μέχρι τον προηγούμενο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Ώρα</th>
                                <th>Συνδέσεις</th>
                                <th>Ποσοστό</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($hourly_statistics_data as $hour_data)
                                <tr>
                                    <td>{{Carbon\Carbon::parse($hour_data->hour)->format("H:i")}}
                                    </td>
                                    <td>{{$hour_data->connections}}
                                    </td>
                                    <td>
                                        @if((int)$total_hourly_connections!==0)
                                            {{number_format((float)($hour_data->connections/$total_hourly_connections)*100,2)}}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <td><strong>Σύνολο</strong></td>
                                <td><strong>{{$total_hourly_connections}}</strong></td>
                                <td><strong>100%</strong></td>
                            </tr>
                            </tbody>

                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12"><h4>Συνολικές συνδέσεις ανά μήνα από 1η Σεπτεμβρίου 2016 μέχρι τον προηγούμενο μήνα</h4></div>
                    <div class="col-md-12">
                        <table style="margin-top:10px; width:100%" cellpadding="0" cellspacing="0" border="0"
                               class="table table-hover table-striped table-bordered" id="participantsTable">
                            <thead>
                            <tr>
                                <th>Μήνας</th>
                                <th>Συνδέσεις</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($monthly_statistics_data as $month_data)
                                <tr>
                                    <td>{{Carbon\Carbon::parse($month_data->month)->format("M-Y")}}
                                    </td>
                                    <td>{{$month_data->connections}}
                                    </td>

                                </tr>
                            @endforeach
                            <tr>
                                <td><strong>Σύνολο</strong></td>
                                <td><strong>{{$total_monthly_connections}}</strong></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
