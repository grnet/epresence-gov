@extends('app')

@section('content')
	<h1>Insert a new Institution</h1>
	<hr/>
	
	{!! Form::open(['url' => 'institutions']) !!}
		<div class"form-group">
			{!! Form::label('title', 'Institution Title:') !!}
			{!! Form::text('title', null, ['class' => 'form-control']) !!}
		</div>
		
		<div class"form-group">
			{!! Form::label('slug', 'Institution Slug:') !!}
			{!! Form::text('slug', null, ['class' => 'form-control']) !!}
		</div>
		
		<div class"form-group">
			{!! Form::submit('Add Institution', ['class' => 'btn btn-primary form-control']) !!}
		</div>
	{!! Form::close() !!}
@stop