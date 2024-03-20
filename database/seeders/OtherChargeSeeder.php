<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OtherCharge;
use Illuminate\Support\Facades\DB;

class OtherChargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('other_charges')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $other_charges = [

            ['min_km_time' => '5','type' => 1,'amount' => '100'],
            ['min_km_time' => '6','type' => 2,'amount' => '50'],
            ['min_km_time' => '3','type' => 3,'amount' => '20'],
        ];

        foreach($other_charges as $other_charge){

            $other_charge_value = new OtherCharge();
            $other_charge_value->min_km_time = $other_charge['min_km_time'];
            $other_charge_value->type = $other_charge['type'];
            $other_charge_value->amount = $other_charge['amount'];
            $other_charge_value->save();
        }
    }
}
