@if(is_null(Session::get('previous_url')))
	{{ Session::put('previous_url', URL::previous()) }}
@endif

<div class="form-group">
	{!! Form::label('FieldTitle', trans('conferences.title').':', ['class' => 'control-label col-sm-2 ']) !!}
	<div class="col-sm-9">
		{!! Form::text('title', null, ['class' => 'form-control', 'id' => 'FieldTitle','maxlength'=>"500"]) !!}
		<div class="help-block with-errors" style="margin:0px;"></div>
	</div>
</div>
@if(Auth::user()->hasRole('SuperAdmin'))
<div class="form-group">
	{!! Form::label('HostUrlAccessible','Host Url Accessible: ', ['class' => 'control-label col-sm-2 ']) !!}
	<div class="col-sm-10">
		@if(class_basename(URL::current()) != 'create')
		{!! Form::checkbox('host_url_accessible', $conference->host_url_accessible, true, ['id' => 'HostUrlAccessible', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
		@else
		{!! Form::checkbox('host_url_accessible', false, true, ['id' => 'HostUrlAccessible', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
		@endif
	</div>
</div>
@endif
@if(class_basename(URL::current()) != 'create')
<div class="form-group">
     <label class="control-label col-sm-2" style="padding-top:0">{{trans('conferences.moderator')}}:</label>
     <div class="col-sm-10 form-control-static" style="padding-top:0">
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
		<div class="input-group date datepicker"  style="width:140px">
			{!! Form::text('start_date', $start_date, ['class' => 'form-control', 'id' => 'FieldStartDate']) !!}
            <span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
   		</div>
	</div>
	<div style="float:left" >
		{!! Form::label('FieldStartTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		@if(isset($default_values['start_hour']) && isset($default_values['start_minute']))
		{!! Form::text('start_time', $start_time, ['class' => 'form-control timepicker', 'id' => 'FieldStartTime', 'style' => 'width:50%;','data-timepicki-tim'=>$default_values['start_hour'],'data-timepicki-mini'=>$default_values['start_minute']]) !!}
		@else
		{!! Form::text('start_time', $start_time, ['class' => 'form-control timepicker', 'id' => 'FieldStartTime', 'style' => 'width:50%;','data-timepicki-tim'=>explode(":",$start_time)[0],'data-timepicki-mini'=>explode(":",$start_time)[1]]) !!}
		@endif
	</div>
</div>

<div class="form-group">
	<label class="control-label col-sm-2 col-xs-12">{{trans('conferences.end')}}:</label>
	<div style="float:left" >
		{!! Form::label('FieldEndDate', trans('conferences.date').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		<div class="input-group date datepicker"  style="width:140px">
			{!! Form::text('end_date', $end_date, ['class' => 'form-control', 'id' => 'FieldEndDate']) !!}
			<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>
		</div>
	</div>
	<div style="float:left;">
		{!! Form::label('FieldEndTime', trans('conferences.time').':', ['class' => 'control-label col-sm-1']) !!}
	</div>
	<div style="float:left" >
		@if(isset($default_values['end_hour']) && isset($default_values['end_minute']))
		{!! Form::text('end_time', $end_time, ['class' => 'form-control timepicker', 'id' => 'FieldEndTime', 'style' => 'width:50%;','data-timepicki-tim'=>$default_values['end_hour'],'data-timepicki-mini'=>$default_values['end_minute']]) !!}
		@else
			{!! Form::text('end_time', $end_time, ['class' => 'form-control timepicker', 'id' => 'FieldEndTime', 'style' => 'width:50%;','data-timepicki-tim'=>explode(":",$end_time)[0],'data-timepicki-mini'=>explode(":",$end_time)[1]]) !!}
		@endif
	</div>
</div>

<div class="form-group">
	{!! Form::label('FieldInvisible', trans('conferences.hidden').':', ['class' => 'control-label col-sm-2 ']) !!}
	<div class="col-sm-10">
		{!! Form::checkbox('invisible', $invisible, true, ['id' => 'FieldInvisible', 'data-toggle' => 'checkbox-x', 'data-size' => 'lg', 'data-three-state' => 'false']) !!}
	</div>
</div>

<div class="form-group" >
	<label for="FieldInvMessage" class="control-label col-sm-2" style="margin-bottom:5px">{{trans('conferences.message')}}:</label>
	<div class="col-sm-9">
		{!! Form::textarea('desc', null, ['class' => 'summernote', 'id' => 'FieldInvMessage']) !!}
	</div>
</div>
<div class="form-group" >
	<div class="col-sm-2 form-control-static " style="padding-top:0">
	</div>
	<div class="col-sm-9 form-control-static " style="margin:10px">
		<span style="font-style: italic;">Type the English title and description message in the field below</span>
		</div>
	<label for="FieldInvMessageEn" class="control-label col-sm-2" style="margin-bottom:5px">Message in English:</label>
	<div class="col-sm-9">
		{!! Form::textarea('descEn', null, ['class' => 'summernote', 'id' => 'FieldInvMessageEn']) !!}
	</div>
</div>
@if(Auth::user()->hasRole('SuperAdmin'))
 <div class="form-group" >
	{!! Form::label('FieldMaxDuration', trans('conferences.maxDuration').':', ['class' => 'control-label col-sm-2 ']) !!}
	<div class="col-sm-2 input-group">
		{!! Form::number('max_duration', $max_duration, ['class' => 'form-control', 'id' => 'FieldMaxDuration']) !!}
		<div class="input-group-addon">λεπτά</div>
	</div>
 </div>
@endif

@if(class_basename(URL::current()) == 'copy')
	<div class="form-group">
		<label class="control-label col-sm-2" for="repeat_type_input" style="padding-top:0">{{trans('conferences.repeat_type')}}:</label>
		<div class="col-sm-2 form-control-static" style="padding-top:0">
			<select name="repeat_type" class="form-control" id="repeat_type_input">
				<option value="never" selected>{{trans('conferences.never')}}</option>
				<option value="month">{{trans('conferences.repeat_month')}}</option>
				<option value="week">{{trans('conferences.repeat_week')}}</option>
			</select>
		</div>
		<div class="col-sm-6">
			<span>{{trans('conferences.recurrent_warning')}}</span>
		</div>
	</div>
	<div class="form-group">
		<label class="control-label col-sm-2" for="repeat_for_input" style="padding-top:0">{{trans('conferences.repeat_for')}}:</label>
		<div class="col-sm-2 form-control-static" style="padding-top:0">
			<select name="repeat_for" class="form-control" id="repeat_for_input">
				<option value="0" selected>0</option>
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="3">4</option>
				<option value="3">5</option>
				<option value="3">6</option>
				<option value="3">7</option>
				<option value="3">8</option>
				<option value="3">9</option>
				<option value="3">10</option>
			</select>

		</div>
	</div>
@endif
<div class="row">
	<div class="col-sm-12" id="TeleInitialSaveGroupButtons">
		<span class="pull-right">      
			{!! Form::submit($submitBtn, ['class' => 'btn btn-primary btn-sm']) !!}
			{!! $copyBtn !!}
			<a href="{{ Session::get('previous_url') }}"><button type="button" class="btn btn-default" id="TeleReturn" >{{trans('conferences.return')}}</button></a>
		</span>
	</div>
</div>
