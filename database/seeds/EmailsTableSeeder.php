<?php

use Illuminate\Database\Seeder;
use App\Email;
use Carbon\Carbon;

class EmailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emails = array(
			array('name' => 'conferenceInvitation','title' => 'Πρόσκληση σε τηλεδιάσκεψη (Invitation to teleconference) ','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'userAccountEnable','title' => 'Ενεργοποίηση λογαριασμού','body' => '<p>Αγαπητέ χρήστη της υπηρεσίας e:Presence,</p><p>Τα στοιχεία σας για την είσοδο στην υπηρεσία είναι:</p>','sender_email' => 'admin@grnet.gr'),
			array('name' => 'adminApplication','title' => 'e:Presence: Αίτημα Νέου Συντονιστή','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'conferenceInvitationReminder','title' => 'e:Presence: Υπενθύμιση Πρόσκλησης σε τηλεδιάσκεψη (Reminder Invitation to teleconference) ','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'conferenceRationalUseNoParticipants','title' => 'e:Presence - απελευθέρωση πόρων – Δεν έχουν εισαχθεί email','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'conferenceRationalUseLessParticipants','title' => 'e:Presence - απελευθέρωση πόρων – Περισσότεροι πόροι δεσμευμένοι','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'conferenceEndNotification','title' => 'e:Presence: Λήξη Τηλεδιάσκεψης','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'conferenceMaintenanceMode','title' => 'e:Presence: Προγραμματισμένη λειτουργία συντήρησης','body' => NULL,'sender_email' => 'no-reply@grnet.gr'),
			array('name' => 'toAllCoordinators','title' => NULL,'body' => NULL,'sender_email' => 'no-reply@grnet.gr')
		);
		
		foreach($emails as $email){
			Email::create([
				'name' => $email['name'],
				'title' => $email['title'],
				'body' => $email['body'],
				'sender_email' => $email['sender_email'],
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}
    }
}
