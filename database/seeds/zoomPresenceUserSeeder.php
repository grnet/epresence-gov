<?php

use App\Department;
use App\Institution;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class zoomPresenceUserSeeder extends Seeder
{
    public function run()
    {

        //Users for first institution

        //Create super admin

        $super_admin = User::create([
            "name" => "Medion7-su@zoom.epresence.grnet.gr",
            "email" => "Medion7-su@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "Medion7-su",
            "lastname" => "Medion7-su",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $super_admin->roles()->attach(1);
        $super_admin->institutions()->attach(1);
        $super_admin->departments()->attach(1);
        

        $super_admin = User::create([
            "name" => "Grnet-su@zoom.epresence.grnet.gr",
            "email" => "Grnet-su@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "Grnet-su",
            "lastname" => "Grnet-su",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $super_admin->roles()->attach(1);
        $super_admin->institutions()->attach(1);
        $super_admin->departments()->attach(1);


        $simple_user = User::create([
            "name" => "staff-GRNET-User@zoom.epresence.grnet.gr",
            "email" => "staff-GRNET-User@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-GRNET-User",
            "lastname" => "staff-GRNET-User",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $simple_user->roles()->attach(4);
        $simple_user->institutions()->attach(1);
        $simple_user->departments()->attach(1);


        $simple_user = User::create([
            "name" => "NOC-GRNET-User@zoom.epresence.grnet.gr",
            "email" => "NOC-GRNET-User@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-GRNET-User",
            "lastname" => "NOC-GRNET-User",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $simple_user->roles()->attach(4);
        $simple_user->institutions()->attach(1);
        $simple_user->departments()->attach(2);



        //Create first department administration of first department of first institution

        $department_admin = User::create([
            "name" => "staff-GRNET-Mod-1@zoom.epresence.grnet.gr",
            "email" => "staff-GRNET-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-GRNET-Mod-1",
            "lastname" => "staff-GRNET-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(1);
        $department_admin->departments()->attach(1);

        //Create second department administration of first department of first institution

        $department_admin = User::create([
            "name" => "staff-GRNET-Mod-2@zoom.epresence.grnet.gr",
            "email" => "staff-GRNET-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-GRNET-Mod-2",
            "lastname" => "staff-GRNET-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(1);
        $department_admin->departments()->attach(1);



        //Create first department administration of second department of first institution

        $department_admin = User::create([
            "name" => "NOC-GRNET-Mod-1@zoom.epresence.grnet.gr",
            "email" => "NOC-GRNET-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-GRNET-Mod-1",
            "lastname" => "NOC-GRNET-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(1);
        $department_admin->departments()->attach(2);

        //Create second department administration of second department of first institution

        $department_admin = User::create([
            "name" => "NOC-GRNET-Mod-2@zoom.epresence.grnet.gr",
            "email" => "NOC-GRNET-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-GRNET-Mod-2",
            "lastname" => "NOC-GRNET-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(1);
        $department_admin->departments()->attach(2);

        //Create 1st institution admin of first institution

        $institution_admin = User::create([
            "name" => "GRNET-Mod-1@zoom.epresence.grnet.gr",
            "email" => "GRNET-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "GRNET-Mod-1",
            "lastname" => "GRNET-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        //Attach institution admin role to the users

        $institution_admin->roles()->attach(2);
        $institution_admin->institutions()->attach(1);
        $institution_admin->departments()->attach(1);

        //Create 2nd institution admin of first institution

        $institution_admin = User::create([
            "name" => "GRNET-Mod-2@zoom.epresence.grnet.gr",
            "email" => "GRNET-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "GRNET-Mod-2",
            "lastname" => "GRNET-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        //Attach institution admin role to the users

        $institution_admin->roles()->attach(2);
        $institution_admin->institutions()->attach(1);
        $institution_admin->departments()->attach(1);



        //Users for second institution

        //Simple users

        $simple_user = User::create([
            "name" => "staff-Inst1-User@zoom.epresence.grnet.gr",
            "email" => "staff-Inst1-User@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-Inst1-User",
            "lastname" => "staff-Inst1-User",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $simple_user->roles()->attach(4);
        $simple_user->institutions()->attach(2);
        $simple_user->departments()->attach(3);


        $simple_user = User::create([
            "name" => "NOC-Inst1-User@zoom.epresence.grnet.gr",
            "email" => "NOC-Inst1-User@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-Inst1-User",
            "lastname" => "NOC-Inst1-User",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $simple_user->roles()->attach(4);
        $simple_user->institutions()->attach(2);
        $simple_user->departments()->attach(4);


        //Department admins


        $department_admin = User::create([
            "name" => "staff-Inst1-Mod-1@zoom.epresence.grnet.gr",
            "email" => "staff-Inst1-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-Inst1-Mod-1",
            "lastname" => "staff-Inst1-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(2);
        $department_admin->departments()->attach(3);

        $department_admin = User::create([
            "name" => "staff-Inst1-Mod-2@zoom.epresence.grnet.gr",
            "email" => "staff-Inst1-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "staff-Inst1-Mod-2",
            "lastname" => "staff-Inst1-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(2);
        $department_admin->departments()->attach(3);

        $department_admin = User::create([
            "name" => "NOC-Inst1-Mod-1@zoom.epresence.grnet.gr",
            "email" => "NOC-Inst1-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-Inst1-Mod-1",
            "lastname" => "NOC-Inst1-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(2);
        $department_admin->departments()->attach(4);

        $department_admin = User::create([
            "name" => "NOC-Inst1-Mod-2@zoom.epresence.grnet.gr",
            "email" => "NOC-Inst1-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "NOC-Inst1-Mod-2",
            "lastname" => "NOC-Inst1-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        $department_admin->roles()->attach(3);
        $department_admin->institutions()->attach(2);
        $department_admin->departments()->attach(4);


        //Create institution admins

        $institution_admin = User::create([
            "name" => "Inst1-Mod-1@zoom.epresence.grnet.gr",
            "email" => "Inst1-Mod-1@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "Inst1-Mod-1",
            "lastname" => "Inst1-Mod-1",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        //Attach institution admin role to the users

        $institution_admin->roles()->attach(2);
        $institution_admin->institutions()->attach(2);
        $institution_admin->departments()->attach(3);

        $institution_admin = User::create([
            "name" => "Inst1-Mod-2@zoom.epresence.grnet.gr",
            "email" => "Inst1-Mod-2@zoom.epresence.grnet.gr",
            "password" => bcrypt(123123),
            "firstname" => "Inst1-Mod-2",
            "lastname" => "Inst1-Mod-2",
            "telephone" => "",
            "persistent_id" => "",
            "thumbnail" => "",
            "status"=>1,
            "confirmation_state"=>"local",
            "state"=>"local",
            "creator_id"=>null,
            "comment"=>"",
            "custom_values"=>'{"institution":"","department":""}',
            "admin_comment"=>"",
            "confirmed"=>1,
            "activation_token"=>"",
            "confirmation_code"=>"",
            "remember_token"=>str_random(16),
            "deleted"=>0,
            "accepted_terms"=>Carbon::now()
        ]);

        //Attach institution admin role to the users

        $institution_admin->roles()->attach(2);
        $institution_admin->institutions()->attach(2);
        $institution_admin->departments()->attach(3);

    }
}
