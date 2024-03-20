<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Language;
use Log;

class MultipleLanguage{

    public static function allLanguages($change_value){

        $languages = Language::get();

        foreach($languages as $language){

            if($language->type == "malayalam"){

               $lcode = "ml";

            }else{

               $lcode = substr($language->type,0,2);
            }

            if($language->type == "english"){

                $lang_name = $change_value;

            }else{

                $lang_name = self::languageChange($change_value,$lcode);

            }

            $language_name_arrays[$language->id] = $lang_name;

         }

        return $language_name_arrays;
    }

	public static function languageChange($name,$lcode){
        $response = Http::asForm()
        ->withoutVerifying()
        ->post('https://translation.googleapis.com/language/translate/v2', [
            'q' => $name,
            'key' => 'AIzaSyBYzFBANdDvRIhwbwvF7VpXCxLZ1qhB8vo',
            'target' => $lcode,
            'source' => 'en',
        ]);

        $get_response = json_decode($response);

          // dd($get_response);

        return $get_response->data->translations[0]->translatedText;

       // return $name;
	}

}
