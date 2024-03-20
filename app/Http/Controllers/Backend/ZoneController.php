<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Helpers\BackendValidation;
use App\Http\Controllers\Controller;
use App\Http\Resources\ZoneResource;
use App\Models\Zone;
use App\Imports\ZoneImport;
use App\Exports\ZoneSampleExport;
use Maatwebsite\Excel\Facades\Excel;

class ZoneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $zones = ZoneResource::collection(Zone::all());

        return response()->json(['status' => 'success', 'zones' => $zones], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = BackendValidation::zoneValidation($request,$id=null);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $zone = new Zone();
        $zone->city = $request->city;
        $zone->zone = $request->zone;
        $zone->pin_code = $request->pin_code;

        $zone->save();

        return response()->json(['status' => 'success','message' => "The Zone Added Successfully"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $zone = new ZoneResource(Zone::findOrFail($id));

        return response()->json(['status' => 'success', 'zone' => $zone], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $zone = new ZoneResource(Zone::findOrFail($id));

        return response()->json(['status' => 'success', 'zone' => $zone], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validate = BackendValidation::zoneValidation($request,$id);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);

        }

        $zone = Zone::find($id);
        $zone->city = $request->city;
        $zone->zone = $request->zone;
        $zone->pin_code = $request->pin_code;

        $zone->save();

        return response()->json(['status' => 'success','message' => "The Zone Updated Successfully"],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $zone = Zone::find($id);

        $zone->delete();

        return response()->json(['status' => 'success', 'message' => "The zone Has Been Deleted"], 200);
    }

    public function importZone(Request $request)
    {
        $validate = BackendValidation::importZoneValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $import = new ZoneImport;

        Excel::import($import,$request->import_excel, 'zones.xlsx');

        $validationErrors = $import->getValidationErrors();

        if ($validationErrors == 'success') {
            return response()->json(['status' => 'success', 'message' => "The zone Has Been Imported"], 200);

        }else{
            return response()->json(['status' => 'error', 'messages' => $validationErrors], 400);
        }
    }

    public function exportZoneSample()
    {
        return Excel::download(new ZoneSampleExport, 'sample_import_file.xlsx');
    }

}
