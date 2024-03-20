<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Admin;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $role = new Role();
        $role->role = 'super admin';
        $role->code ='SUPER ADMIN';
        $role->status = 1;
        $role->save();

        $admin = new Admin();
        $admin->name = 'karthi';
        $admin->username = 'admin';
        $admin->password = 'admin@123';
        $admin->mobile = '9566673188';
        $admin->email = 'karthi@gmail.com';
        $admin->role_id = $role->id;
        $admin->status = 1;
        $admin->save();


        $permissions = Permission::where('status',1)->get();

        foreach ($permissions as  $permission) {
        	
        	DB::table('permission_role')->insert([
			    'permission_id' => $permission->id,
			    'role_id' => $role->id
			]);

        }
    }
}
