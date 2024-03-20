<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class ZoneSampleExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([
            ['City','Zone','Pin Code'],

            ['Sample Data',	'Sample Data','123456'],
        ]);
    }
}
