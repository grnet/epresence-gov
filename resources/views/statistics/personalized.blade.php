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
        .container
        {
            min-width: 400px !important;
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
                            <li class="active"><a href="#">{!!trans('statistics.personalized')!!}</a></li>
                            @if(Auth::user()->hasRole('SuperAdmin'))
                                <li><a href="/statistics/report">{!!trans('statistics.usageReports')!!}</a></li>
                                <li><a href="/statistics/demo-room">Demo Room</a></li>
                                <li><a href="/statistics/utilization">Utilization</a></li>
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="well well-sm" style="margin-top:50px;">
                    <h3>{!! trans('account.total') !!}</h3>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <h4><strong>{!! trans('account.user_statistics') !!}</strong></h4>
                            <span class="account_h">{!! trans('account.total_conferences_joined') !!}</span> {{$statistics['all_time']['total_conferences_joined']}} {{trans('account.from_invited',['conf_invited'=>$statistics['all_time']['total_conferences_invited']])}}<br/>
                            <span class="account_h">{!! trans('account.connection_type') !!}</span><br/>
                            {{$statistics['all_time']['conferences_joined_by_type']['Desktop-Mobile']}} {!! trans('account.times') !!} Desktop-Mobile <br/>
                            <span class="account_h">{!! trans('account.total_duration_joined') !!}</span>
                            @if($statistics['all_time']['total_duration_joined']['hours']>0)
                                @if($statistics['all_time']['total_duration_joined']['hours']>1)
                                    {{$statistics['all_time']['total_duration_joined']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                @else
                                    {{$statistics['all_time']['total_duration_joined']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                @endif
                            @endif
                            {{$statistics['all_time']['total_duration_joined']['minutes']}} {!! trans('application.minutes') !!}
                        </div>
                        @if($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin'))
                            <div class="col-sm-12 col-md-6">
                                <h4><strong>{!! trans('account.moderator_statistics') !!}</strong></h4>
                                <span class="account_h">{!! trans('account.total_conferences_created') !!}</span> {{$statistics['all_time']['total_conferences_created']}} <br/>
                                <span class="account_h">{!! trans('account.total_duration') !!}</span>
                                @if($statistics['all_time']['total_duration']['hours']>0)
                                    @if($statistics['all_time']['total_duration']['hours']>1)
                                        {{$statistics['all_time']['total_duration']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                    @else
                                        {{$statistics['all_time']['total_duration']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                    @endif
                                @endif
                                {{$statistics['all_time']['total_duration']['minutes']}} {!! trans('application.minutes') !!}
                                <br/>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="well well-sm">
                    <h3>{!! trans('account.current_year') !!} {{Carbon\Carbon::now()->year}}</h3>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <h4><strong>{!! trans('account.user_statistics') !!}</strong></h4>
                            <span class="account_h">{!! trans('account.total_conferences_joined') !!}</span> {{$statistics['current_year']['total_conferences_joined']}} {{trans('account.from_invited',['conf_invited'=>$statistics['current_year']['total_conferences_invited']])}} <br/>
                            <span class="account_h">{!! trans('account.connection_type') !!}</span><br/>
                            {{$statistics['current_year']['conferences_joined_by_type']['Desktop-Mobile']}} {!! trans('account.times') !!} Desktop-Mobile <br/>
                            <span class="account_h">{!! trans('account.total_duration_joined') !!}</span>
                            @if($statistics['current_year']['total_duration_joined']['hours']>0)
                                @if($statistics['current_year']['total_duration_joined']['hours']>1)
                                    {{$statistics['current_year']['total_duration_joined']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                @else
                                    {{$statistics['current_year']['total_duration_joined']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                @endif
                            @endif
                            {{$statistics['current_year']['total_duration_joined']['minutes']}} {!! trans('application.minutes') !!}
                        </div>
                        @if($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin'))
                            <div class="col-sm-12 col-md-6">
                                <h4><strong>{!! trans('account.moderator_statistics') !!}</strong></h4>
                                <span class="account_h">{!! trans('account.total_conferences_created') !!}</span> {{$statistics['current_year']['total_conferences_created']}} <br/>
                                <span class="account_h">{!! trans('account.total_duration') !!}</span>
                                @if($statistics['current_year']['total_duration']['hours']>0)
                                    @if($statistics['current_year']['total_duration']['hours']>1)
                                        {{$statistics['current_year']['total_duration']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                    @else
                                        {{$statistics['current_year']['total_duration']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                    @endif
                                @endif
                                {{$statistics['current_year']['total_duration']['minutes']}} {!! trans('application.minutes') !!}
                                <br/>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="well well-sm">
                    <h3>{!! trans('account.previous_year') !!} {{Carbon\Carbon::now()->subYear()->year}}</h3>
                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <h4><strong>{!! trans('account.user_statistics') !!}</strong></h4>
                            <span class="account_h">{!! trans('account.total_conferences_joined') !!}</span> {{$statistics['previous_year']['total_conferences_joined']}} {{trans('account.from_invited',['conf_invited'=>$statistics['previous_year']['total_conferences_invited']])}} <br/>
                            <span class="account_h">{!! trans('account.connection_type') !!}</span><br/>
                            {{$statistics['previous_year']['conferences_joined_by_type']['Desktop-Mobile']}} {!! trans('account.times') !!} Desktop-Mobile <br/>
                            <span class="account_h">{!! trans('account.total_duration_joined') !!}</span>
                            @if($statistics['previous_year']['total_duration_joined']['hours']>0)
                                @if($statistics['previous_year']['total_duration_joined']['hours']>1)
                                    {{$statistics['previous_year']['total_duration_joined']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                @else
                                    {{$statistics['previous_year']['total_duration_joined']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                @endif
                            @endif
                            {{$statistics['previous_year']['total_duration_joined']['minutes']}} {!! trans('application.minutes') !!}
                        </div>
                        @if($user->hasRole('DepartmentAdministrator') || $user->hasRole('InstitutionAdministrator') || $user->hasRole('SuperAdmin'))
                            <div class="col-sm-12 col-md-6">
                                <h4><strong>{!! trans('account.moderator_statistics') !!}</strong></h4>
                                <span class="account_h">{!! trans('account.total_conferences_created') !!}</span> {{$statistics['previous_year']['total_conferences_created']}} <br/>
                                <span class="account_h">{!! trans('account.total_duration') !!}</span>
                                @if($statistics['previous_year']['total_duration']['hours']>0)
                                    @if($statistics['previous_year']['total_duration']['hours']>1)
                                        {{$statistics['previous_year']['total_duration']['hours']}} {!! trans('statistics.hours') !!} {!! trans('site.and') !!}
                                    @else
                                        {{$statistics['previous_year']['total_duration']['hours']}} {!! trans('site.hour') !!} {!! trans('site.and') !!}
                                    @endif
                                @endif
                                {{$statistics['previous_year']['total_duration']['minutes']}} {!! trans('application.minutes') !!}
                                <br/>
                            </div>
                        @endif
                    </div>
                </div>
            </div><!--/.box-->
        </div><!--/.container-->
    </section>
@endsection
