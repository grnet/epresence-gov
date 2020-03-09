{!! trans('emails.delete_unconfirmed_user_intro') !!}
<ul>
	@foreach($unconfirmedUsers as $unconfirmedUser)
		<li>ID: {!! $unconfirmedUser->id !!}, email: {!! $unconfirmedUser->email !!}, creation date: {!! $unconfirmedUser->created_at !!}, deletion date: {!! $now !!}</li>
	@endforeach
</ul>