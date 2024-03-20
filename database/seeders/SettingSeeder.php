<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('settings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $settings = [//type 1->subscription, 2->tax percentage, 3->reached amount
            
            ['type' => 1,'value' => '300'],
            ['type' => 2,'value' => '5'],
            ['type' => 3,'value' => '200'],
        ];

        foreach($settings as $setting){

            $setting_value = new Setting();
            $setting_value->type = $setting['type'];
            $setting_value->value = $setting['value'];
            $setting_value->save();
        }

    }
}
