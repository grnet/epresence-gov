<p>Μόλις δημιουργήθηκε ένας λογαριασμός  
@if($user->roles->first()->name == "EndUser")
	Χρήστη
@elseif($user->roles->first()->name == "DepartmentAdministrator")
	Συντονιστή Τμήματος
@elseif($user->roles->first()->name == "InstitutionAdministrator")
	Συντονιστή Οργανισμού
@endif

@if($user->state == 'local')
για την υπηρεσία τηλεδιασκέψεων e:Presence του ΕΔΕΤ. Τα στοιχεία σας για την είσοδο στην υπηρεσία είναι:</p>

<p>Email: {!! $user->email !!}<br/>Password: {!! $password !!}</p>

<p>Μπορείτε να συνδεθείτε στην υπηρεσία εδώ <a href="{!! $login_url !!}">{!! $login_url !!}</a>. 
@if($user->confirmed == 0)
	Την πρώτη φορά που θα συνδεθείτε θα σας ζητηθεί να επιβεβαιώσετε τα στοιχεία σας για να ενεργοποιήσετε τον λογαριασμό σας.
@endif
</p>

@elseif($user->state == 'sso')

για την υπηρεσία τηλεδιασκέψεων e:Presence του ΕΔΕΤ.</p>

<p>Μπορείτε να συνδεθείτε στην υπηρεσία εδώ <a href="{!! $login_url !!}">{!! $login_url !!}</a> και να πατήσετε το κουμπί "Είσοδος μέσω Κεντρικής Υπηρεσίας Ταυτοποίησης". Την πρώτη φορά που θα συνδεθείτε θα σας ζητηθεί να επιβεβαιώσετε τα στοιχεία σας για να ενεργοποιήσετε τον λογαριασμό σας.</p>

@endif


@if($user->roles->first()->name == 'EndUser')

	<p>Αποθηκεύστε αυτό το email, γιατί θα το χρειαστείτε όταν λάβετε πρόσκληση από κάποιον συντονιστή για συμμετοχή σε τηλεδιάσκεψη μέσω της υπηρεσίας e:Presence.</p>
	
@endif

<p>Με εκτίμηση,<br/>e:Presence support team</p>

<hr>

<p>A new 
@if($user->roles->first()->name == "EndUser")
	User
@elseif($user->roles->first()->name == "DepartmentAdministrator")
	Department Moderator
@elseif($user->roles->first()->name == "InstitutionAdministrator")
	Organization  Moderator
@endif

@if($user->state == 'local')
account has just been created for the e:Presence teleconference service of GRNET. Your credentials for accessing the service are:</p>

<p>Email: {!! $user->email !!}<br/>Password: {!! $password !!}</p>

<p>You can login to the service from here: <a href="{!! $login_url !!}">{!! $login_url !!}</a>. 
@if($user->confirmed == 0)
	At your first login attempt you will be asked to confirm your account data and to activate your account.
@endif
</p>

@elseif($user->state == 'sso')

account has just been created for the e:Presence teleconference service of GRNET.</p>

<p>You can login to the service from here: <a href="{!! $login_url !!}">{!! $login_url !!}</a> and click the "Login through GRNET's Authentication and Authorization Infrastructure". At your first login attempt you will be asked to confirm your account data and to activate your account.</p>

@endif

@if($user->roles->first()->name == 'EndUser')
	
	<p>Please archive this email. Υou will need it when you will be invited by a moderator to join a teleconference meeting through the e:Presence teleconference service.</p>
	
@endif

<p>Yours sincerely,<br/>e:Presence support team</p>