<?php

use Illuminate\Database\Seeder;
use App\Institution;
use App\Department;
use Carbon\Carbon;

class InstitutionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $institutions = array();
		$institutions [] = ['title' => 'ΕΔΕΤ', 'slug' => 'grnet', 'url' => 'https://www.grnet.gr/', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		$institutions [] = ['title' => 'Άλλο', 'slug' => 'other', 'url' => null, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		
		foreach($institutions as $institution){
			$new_institution = Institution::create([
									'title' => $institution['title'],
									'slug' => $institution['slug'],
									'url' => $institution['url'],
									'created_at' => $institution['created_at'],
									'updated_at' => $institution['updated_at'],
								]);
								
			Department::create(['title' => 'Διοίκηση', 'slug' => 'admin', 'institution_id' => $new_institution->id, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
		}
		
		$other_institution = Institution::where('slug', 'other')->first()->id;
		Department::create(['title' => 'Άλλο', 'slug' => 'other', 'institution_id' => $other_institution, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
    }
}
