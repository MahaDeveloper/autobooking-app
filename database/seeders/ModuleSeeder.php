<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('modules')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $modules = ['Dashboard',
                    'Roles',
                    'Admins',
                    'users',
                    'Drivers',
                    'Subscriptions',
                    'Booking History',
                    'Pay Tax',
                    'Fare price',
                    'Rides',
                    'Rewards',
                    'Reports',
                    'Refer And Earn',
                    'Activity Log',
                    'Notification',
                    'Review And Rating',
                    'Offline Boooking',
                    'Help And Supports'];

        $permissions = ['List','Add','Edit','View','Status','Delete'];

         foreach($modules as $module){

        	$modulesave = new Module();
        	$modulesave->module_type = $module;
        	$modulesave->code = $module;
        	$modulesave->save();

        	foreach($permissions as $permission){
        		$persave = new Permission();
        		$persave->module_id = $modulesave->id;
        		$persave->permission_type = $permission;
        		$persave->code = 'CAN-'.strtoupper($permission).'-'.strtoupper($module);
        		$persave->save();
        	}
        }
    }
}
