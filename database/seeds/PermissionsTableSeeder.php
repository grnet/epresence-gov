<?php

use Illuminate\Database\Seeder;
use App\Permission;
use App\Role;
use Carbon\Carbon;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = array(
			array('name' => 'view_conferences','label' => 'View conferences', 'roles' => array('SuperAdmin', 'InstitutionAdministrator', 'DepartmentAdministrator')),
			array('name' => 'view_users','label' => 'View users', 'roles' => array('SuperAdmin')),
			array('name' => 'view_institutions','label' => 'View institutions', 'roles' => array('SuperAdmin')),
			array('name' => 'edit_any_conference','label' => 'The user can edit any Conference', 'roles' => array('SuperAdmin')),
			array('name' => 'edit_institution_conference','label' => 'The user can edit any of Institution\'s Conference', 'roles' => array('InstitutionAdministrator')),
			array('name' => 'edit_department_conference','label' => 'The user can edit any of the Departments\'s Conference', 'roles' => array('DepartmentAdministrator')),
			array('name' => 'create_conference','label' => 'The user can create a Conference', 'roles' => array('SuperAdmin', 'InstitutionAdministrator', 'DepartmentAdministrator')),
			array('name' => 'edit_conferences','label' => 'The user can edit a Conference', 'roles' => array('SuperAdmin', 'InstitutionAdministrator', 'DepartmentAdministrator')),
			array('name' => 'view_admins_menu','label' => 'View Administrators', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'view_users_menu','label' => 'View users\' menu', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'view_user_settings','label' => 'View user settings', 'roles' => array('SuperAdmin')),
			array('name' => 'create_org_admin','label' => 'Create Organization Admin', 'roles' => array('SuperAdmin')),
			array('name' => 'create_dep_admin','label' => 'Create Department Admin', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'edit_org_admin','label' => 'Edit Organization Admin', 'roles' => array('SuperAdmin')),
			array('name' => 'edit_dep_admin','label' => 'Edit Department Admin', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'edit_user','label' => 'Edit user', 'roles' => array('SuperAdmin')),
			array('name' => 'delete_user','label' => 'Delete User Account', 'roles' => array('SuperAdmin')),
			array('name' => 'delete_org_admin','label' => 'Delete Institution Admin', 'roles' => array('SuperAdmin')),
			array('name' => 'delete_dep_admin','label' => 'Delete Department Admin', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'view_admins','label' => 'View Super Admins', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'view_org_admins','label' => 'View Institution Admins', 'roles' => array('SuperAdmin')),
			array('name' => 'view_dep_admins','label' => 'View Department Admins', 'roles' => array('SuperAdmin', 'InstitutionAdministrator')),
			array('name' => 'view_applications','label' => 'View Applications Tab', 'roles' => array('SuperAdmin')),
			array('name' => 'delete_any_conference','label' => 'Delete any conference', 'roles' => array('SuperAdmin'))
		);
		
		foreach($permissions as $permission){
			$new_permission = Permission::create([
								'name' => $permission['name'],
								'label' => $permission['label'],
								'created_at' => Carbon::now(),
								'updated_at' => Carbon::now(),
							]);
							
			foreach($permission['roles'] as $role){
				$new_permission->assignRole($role);
			}
		}
    }
}
