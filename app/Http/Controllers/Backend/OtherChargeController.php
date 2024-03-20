<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OtherChargeResource;
use App\Models\OtherCharge;
use App\Helpers\BackendValidation;

class OtherChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $other_charges = OtherChargeResource::collection(OtherCharge::all());

        return response()->json(['status' => 'success', 'other_charges' => $other_charges], 200);
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
        $validate = BackendValidation::otherChargeValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $exist_type = OtherCharge::where('type',$request->type)->first();

        if($exist_type){
            return response()->json(['status' => 'error', 'message' => "The Charge Type Has Already Exist, You Can Edit the Data"], 400);
        }

        $other_charge = new OtherCharge;
        $other_charge->min_km_time = $request->min_km_time;
        $other_charge->amount = $request->amount;
        $other_charge->type = $request->type;
        $other_charge->save();

        return response()->json(['status' => 'success', 'message' => "The Other Charge Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $other_charge = new OtherChargeResource(OtherCharge::findOrFail($id));

        return response()->json(['status' => 'success', 'other_charge' => $other_charge], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id)
    {
        //
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
        $validate = BackendValidation::otherChargeValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $other_charge = OtherCharge::find($id);
        $other_charge->min_km_time = $request->min_km_time;
        $other_charge->amount = $request->amount;
        $other_charge->type = $request->type;
        $other_charge->save();

        return response()->json(['status' => 'success', 'message' => "The Other Charge Has Been Updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $other_charge = OtherCharge::find($id);

        $other_charge->delete();

        return response()->json(['status' => 'success', 'message' => "The Other Charge Has Been Deleted"], 200);
    }
}
