<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Language;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('languages')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $languages = [
            ['name'=>'English', 'type'=>'english'],
            ['name'=>'தமிழ்','type'=>'tamil'],
            ['name'=>'हिन्दी','type'=>'hindi'],
            ['name'=>'ಕನ್ನಡ','type'=>'kannada'],
            ['name'=>'മലയാളം','type'=>'malayalam'],
            ['name'=>'తెలుగు','type'=>'telugu'],
        ];

        foreach($languages as $language)
        {
            $lang = new Language();
            $lang->name = $language['name'];
            $lang->type = $language['type'];
            $lang->save();
         }
    }
}
