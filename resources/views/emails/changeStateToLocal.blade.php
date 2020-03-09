<p>Ο τύπος του λογαριασμού σας για την υπηρεσία τηλεδιασκέψεων e:Presence του ΕΔΕΤ άλλαξε. Τα στοιχεία σας για την είσοδο στην υπηρεσία είναι:</p>

<p>Email: {!! $user->email !!}<br/>Password: {!! $password !!}</p>

<p>Μπορείτε να συνδεθείτε στην υπηρεσία εδώ <a href="{!! $login_url !!}">{!! $login_url !!}</a>.</p>

@if($user->roles->first()->name == 'EndUser')

	<p>Αποθηκεύστε αυτό το email, γιατί θα το χρειαστείτε όταν λάβετε πρόσκληση από κάποιον συντονιστή για συμμετοχή σε τηλεδιάσκεψη μέσω της υπηρεσίας e:Presence.</p>
	
@endif

<p>Με εκτίμηση,<br/>e:Presence support team</p>

<hr>

<p>The state of your account has changed for the e:Presence teleconference service of GRNET. Your credentials for accessing the service are:</p>

<p>Email: {!! $user->email !!}<br/>Password: {!! $password !!}</p>

<p>You can login to the service from here: <a href="{!! $login_url !!}">{!! $login_url !!}</a>.</p>

@if($user->roles->first()->name == 'EndUser')
	
	<p>Please archive this email. Υou will need it when you will be invited by a moderator to join a teleconference meeting through the e:Presence teleconference service.</p>
	
@endif

<p>Yours sincerely,<br/>e:Presence support team</p>