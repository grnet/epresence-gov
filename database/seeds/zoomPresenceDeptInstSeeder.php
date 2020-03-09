<?php

use App\Department;
use App\Institution;
use Illuminate\Database\Seeder;


class zoomPresenceDeptInstSeeder extends Seeder
{
    public function run()
    {
        //Seed first institution

        $inst1 = Institution::create(["title" => "GRNET", "slug" => "NoID", "status" => "1", "url" => "grnet.gr", "shibboleth_domain" => "grnet.gr"]);

        Department::create(["title" => "staff-GRNET", "slug" => "admin", "status" => "1", "institution_id" => $inst1->id]);
        Department::create(["title" => "NOC-GRNET", "slug" => "NoID", "status" => "1", "institution_id" => $inst1->id]);
        Department::create(["title" => "other-GRNET", "slug" => "other", "status" => "1", "institution_id" => $inst1->id]);

        //Seed second institution

        $inst2 = Institution::create(["title" => "Inst1", "slug" => "NoID", "status" => "1", "url" => "inst1.gr", "shibboleth_domain" => "inst1.gr"]);

        //Seed second institution's departments

        Department::create(["title" => "staff-Inst1", "slug" => "admin", "status" => "1", "institution_id" => $inst2->id]);
        Department::create(["title" => "NOC-Inst1", "slug" => "NoID", "status" => "1", "institution_id" => $inst2->id]);
        Department::create(["title" => "other-Inst1", "slug" => "other", "status" => "1", "institution_id" => $inst2->id]);
    }
}
