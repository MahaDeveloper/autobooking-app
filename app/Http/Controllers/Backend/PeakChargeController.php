<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PeakChargeResource;
use App\Models\PeakCharge;
use App\Helpers\BackendValidation;

class PeakChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $peack_charges = PeakChargeResource::collection(PeakCharge::all());

        return response()->json(['status' => 'success', 'peack_charges' => $peack_charges], 200);
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
        $validate = BackendValidation::peakChargeValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $peak_charge = new PeakCharge;
        $peak_charge->from_time = $request->from_time;
        $peak_charge->to_time = $request->to_time;
        $peak_charge->percentage = $request->percentage;
        $peak_charge->type = $request->type;
        $peak_charge->save();

        return response()->json(['status' => 'success', 'message' => "The Peak Charge Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $peak_charge = new PeakChargeResource(PeakCharge::findOrFail($id));

        return response()->json(['status' => 'success', 'peak_charge' => $peak_charge], 200);
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
        $validate = BackendValidation::peakChargeValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $peak_charge = PeakCharge::find($id);
        $peak_charge->from_time = $request->from_time;
        $peak_charge->to_time = $request->to_time;
        $peak_charge->percentage = $request->percentage;
        $peak_charge->type = $request->type;
        $peak_charge->save();

        return response()->json(['status' => 'success', 'message' => "The Peak Charge Has Been Updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $peak_charge = PeakCharge::find($id);

        $peak_charge->delete();

        return response()->json(['status' => 'success', 'message' => "The Peak Charge Has Been Deleted"], 200);
    }
}
