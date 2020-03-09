<?php

use Illuminate\Database\Seeder;
use App\Role;
use Carbon\Carbon;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$roles = array();
		$roles[] = ['name' => 'SuperAdmin', 'label' => 'Διαχειριστής', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		$roles[] = ['name' => 'InstitutionAdministrator', 'label' => 'Συντονιστής Οργανισμού', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		$roles[] = ['name' => 'EndUser', 'label' => 'Χρήστης', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		$roles[] = ['name' => 'DepartmentAdministrator', 'label' => 'Συντονιστής Τμήματος', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()];
		
		foreach($roles as $role){
			Role::create([
				'name' => $role['name'],
				'label' => $role['label'],
				'created_at' => $role['created_at'],
				'updated_at' => $role['updated_at']
			]);
		}
    }
}
