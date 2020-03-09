<?php

use Illuminate\Database\Seeder;

use App\Document;

class DocumentSeeder extends Seeder
{
    public function run()
    {

        $new_document = new Document;

        $new_document->title_en = "User Guide";
        $new_document->title_el = "Εγχειρίδιο Χρήστη";


        $new_document->en_file = "/docs/user_guide_en_v3.pdf";
        $new_document->el_file = "/docs/userguidegr.pdf";


        $new_document->en_file_url = null;
        $new_document->el_file_url = null;

        $new_document->save();

        //

        $new_document = new Document;

        $new_document->title_en = "Moderator's guide";
        $new_document->title_el = "Εγχειρίδιο Συντονιστή";

        $new_document->en_file = null;
        $new_document->el_file = "/docs/moderators_guide_gr_v1.pdf";

        $new_document->en_file_url = null;
        $new_document->el_file_url = null;

        $new_document->save();

        //

        $new_document = new Document;

        $new_document->title_en = "A practical guide for choosing AV equipment";
        $new_document->title_el = "Πρακτικός οδηγός επιλογής οπτικοακουστικού εξοπλισμού";

        $new_document->en_file = null;
        $new_document->el_file = "/docs/epresence_best_practice_audiovisual.pdf";

        $new_document->en_file_url = null;
        $new_document->el_file_url = null;

        $new_document->save();

        //

        $new_document = new Document;

        $new_document->title_en = "e:Presence fully meets the requirements as outlined in the Government Gazette (sheet No. 433, March 17, 2011, no. F.122.1/42/23076/B2)";
        $new_document->title_el = "Συμμόρφωση e:Presence με τις απαιτήσεις του ΦΕΚ (Αρ. φύλλου 433, 17 Μαρτίου 2011, Αρ. Φ.122.1/42/23076/Β2)";

        $new_document->en_file = null;
        $new_document->el_file = "/docs/eP_FEK_DEC2011.pdf";

        $new_document->en_file_url = null;
        $new_document->el_file_url = null;

        $new_document->save();

        //

        $new_document = new Document;

        $new_document->title_en = "Indicative compatible AV equipment for users";
        $new_document->title_el = "Ενδεικτικός συμβατός οπτικοακουστικός εξοπλισμός τελικού χρήστη";

        $new_document->en_file = null;
        $new_document->el_file = null;

        $new_document->en_file_url = "http://www.vidyo.com/services-support/technical-support/peripherals/";
        $new_document->el_file_url = null;

        $new_document->save();

    }
}
