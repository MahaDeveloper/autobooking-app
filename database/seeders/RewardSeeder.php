<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Reward;

class RewardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ride_amounts = [

            ['ride_amount' => '150','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '450','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '750','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '900','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '1200','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '1500','reward_amount' => 60,'validity' => '30'],
            ['ride_amount' => '1650','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '1950','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '2250','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '2400','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '2700','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '3000','reward_amount' => 60,'validity' => '30'],
            ['ride_amount' => '3150','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '3450','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '3750','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '3900','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '4200','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '4500','reward_amount' => 60,'validity' => '30'],
            ['ride_amount' => '4650','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '4950','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '5250','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '5400','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '5700','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '6000','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '6150','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '6450','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '6750','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '6900','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '7200','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '7500','reward_amount' => 30,'validity' => '30'],
            ['ride_amount' => '7650','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '7950','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '8250','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '8400','reward_amount' => 10,'validity' => '30'],
            ['ride_amount' => '8700','reward_amount' => 20,'validity' => '30'],
            ['ride_amount' => '9000','reward_amount' => 30,'validity' => '30'],

        ];

        foreach($ride_amounts as $amount_of){

            $ride_amount = new Reward();
            $ride_amount->ride_amount = $amount_of['ride_amount'];
            $ride_amount->reward_amount = $amount_of['reward_amount'];
            $ride_amount->validity = $amount_of['validity'];
            $ride_amount->save();
        }
    }
}
