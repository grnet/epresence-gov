@if(is_null(Session::get('previous_url')))
	{{ Session::put('previous_url', URL::previous()) }}
@endif
<div class="form-group">
	{!! Form::label('FieldTitle', trans('conferences.title').':', ['class' => 'control-label col-sm-2 ']) !!}
	<div class="col-sm-9">
		@if(class_basename(URL::current()) != 'create')
		{!! Form::text('title', null, ['class' => 'form-control', 'id' => 'FieldTitle','maxlength'=>"500"]) !!}
		@else
		{!! Form::text('title', $default_values['title'], ['class' => 'form-control', 'id' => 'FieldTitle','maxlength'=>"500"]) !!}
		@endif
		<div class="help-block with-errors" style="margin:0px;"></div>
	</div>
</div>
@if(class_basename(URL::current()) != 'create')
<div class="form-group">
     <label class="control-label col-sm-2" style="padding-top:0px">{{trans('conferences.moderator')}}:</label>
     <div class="col-sm-10 form-control-static" style="padding-top:0px">
         {{ $conference->user->lastname }} {{  $conference->user->firstname }} 
		@if( $conference->user->hasRole('SuperAdmin'))
			<i><small> — {{trans('conferences.admin')}}</small></i>						
		@elseif($conference->user->hasRole('InstitutionAdministrator') || $conference->user->hasRole('DepartmentAdministrator'))
			<i><small> — {{$conference->user->institutions->first()->title }} 
			@if($conference->user->hasRole('DepartmentAdministrator'))
				&nbsp;({{ $conference->user->departments->first()->title }})
			@endif
			</small></i>
		@endif
    </div>
</div>
{!! Form::hidden('institution_id', $conference->institution->id) !!}
{!! Form::hidden('department_id', $conference->department->id) !!}
@else
<div class="form-group">
     <label class="control-label col-sm-2" style="padding-top:0px">{{trans('conferences.moderator')}}:</label>
     <div class="col-sm-10 form-control-static" style="padding-top:0px">
         {{  Auth::user()->lastname }} {{  Auth::user()->firstname }} 
		@if(Auth::user()->hasRole('SuperAdmin'))
			<i><small> — {{trans('conferences.admin')}}</small></i>						
		@elseif(Auth::user()->hasRole('InstitutionAdministrator') || Auth::user()->hasRole('DepartmentAdministrator'))
			<i><small>— {{ Auth::user()->institutions->first()->title }}</small></i>						
		@endif
    </div>
</div>
{!! Form::hidden('institution_id', Auth::user()->institutions()->first()->id) !!}
{!! Form::hidden('department_id', Auth::user()->departments()->first()->id) !!}
@endif
<div class="form-group">
	<label class="control-label col-sm-2 col-xs-12">{{trans('conferences.start')}}:</label>
    <div style="float:left" >
		{!! Form::label('FieldStartDate', trans('conferences.date').':', ['class' => 'control-label col-sm-1']) !!}
    </div>
    <div style="float:left" >
		<div class="input-group date datepicker"  style="width:150px">
			{!! Form::text('start_date', $start_date, ['disabled'=>true,'class' => 'form-control', 'id' => 'FieldStartDate']) !!}
            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   		</div>
	</div>
	<div style="float:left" >
		{!! Form::label('FieldStartTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		@if(isset($default_values['start_hour']) && isset($default_values['start_minute']))
		{!! Form::text('start_time', $start_time, ['disabled'=>true,'class' => 'form-control timepicker', 'id' => 'FieldStartTime', 'style' => 'width:50%;','data-timepicki-tim'=>$default_values['start_hour'],'data-timepicki-mini'=>$default_values['start_minute']]) !!}
		@else
		{!! Form::text('start_time', $start_time, ['disabled'=>true,'class' => 'form-control timepicker', 'id' => 'FieldStartTime', 'style' => 'width:50%;','data-timepicki-tim'=>explode(":",$start_time)[0],'data-timepicki-mini'=>explode(":",$start_time)[1]]) !!}
		@endif
	</div>
</div>
<div class="form-group">
	<label class="control-label col-sm-2 col-xs-12">{{trans('conferences.end')}}:</label>
	<div style="float:left" >
		{!! Form::label('FieldEndDate', trans('conferences.date').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		<div class="input-group date datepicker"  style="width:150px">
			{!! Form::text('end_date', $end_date, ['disabled'=>true,'class' => 'form-control', 'id' => 'FieldEndDate']) !!}
			<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		</div>
	</div>
	<div style="float:left;">
		{!! Form::label('FieldEndTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		@if(isset($default_values['end_hour']) && isset($default_values['end_minute']))
		{!! Form::text('end_time', $end_time, ['disabled'=>true,'class' => 'form-control timepicker', 'id' => 'FieldEndTime', 'style' => 'width:50%;','data-timepicki-tim'=>$default_values['end_hour'],'data-timepicki-mini'=>$default_values['end_minute']]) !!}
		@else
		{!! Form::text('end_time', $end_time, ['disabled'=>true,'class' => 'form-control timepicker', 'id' => 'FieldEndTime', 'style' => 'width:50%;','data-timepicki-tim'=>explode(":",$end_time)[0],'data-timepicki-mini'=>explode(":",$end_time)[1]]) !!}
		@endif
	</div>
</div>
<div class="row">
	<div class="col-sm-12" id="TeleInitialSaveGroupButtons">
		<span class="pull-right">      
			{!! Form::submit($submitBtn, ['class' => 'btn btn-primary btn-sm']) !!}
			{!! $copyBtn !!}
			<a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default" id="TeleReturn" >{{trans('conferences.return')}}</button></a>
		</span>
	</div>
</div>
