<?php

namespace App\Imports;

use App\Models\Zone;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Row;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Log;

class ZoneImport implements ToCollection, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    private $validateErrors = 0;

    public function collection(Collection $rows)
    {
        $data = $rows->toArray();

        log::info($data);

        $validator = Validator::make($data, [
            '*.city' => 'required',
            '*.zone' => 'required',
            '*.pin_code' => 'required|unique:zones',
        ]);

        if ($validator->fails()) {

            $this->validateErrors = $validator->errors()->first();

        } else {

            foreach($rows as $row){
                $rowData = $row->toArray();
                $zone = new Zone();
                $zone->city = $rowData['city'];
                $zone->zone = $rowData['zone'];
                $zone->pin_code = $rowData['pin_code'];
                $zone->save();
            }

            $this->validateErrors = 'success';
        }
    }

    public function getValidationErrors(): string
    {
        return $this->validateErrors;
    }
}
