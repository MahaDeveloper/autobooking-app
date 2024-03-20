<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use App\Helpers\BackendValidation;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = SettingResource::collection(Setting::all());

        return response()->json(['status' => 'success', 'settings' => $settings], 200);
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
        $validate = BackendValidation::settingValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $exist_type = Setting::where('type',$request->type)->first();

        if($exist_type){
            return response()->json(['status' => 'success', 'message' => "The setting Type Has Already Exist, You Can Edit the Data"], 400);
        }

        $setting = new Setting();
        $setting->type = $request->type;
        $setting->value = $request->value;
        $setting->save();

        return response()->json(['status' => 'success', 'message' => "The Setting Has Been updated"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $setting = new SettingResource(Setting::findOrFail($id));

        return response()->json(['status' => 'success', 'setting' => $setting], 200);
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
        $validate = BackendValidation::settingValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $setting = Setting::find($id);
        $setting->type = $request->type;
        $setting->value = $request->value;
        $setting->save();

        return response()->json(['status' => 'success', 'message' => "The Setting Has Been updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $setting = Setting::find($id);

        $setting->delete();

        return response()->json(['status' => 'success', 'message' => "The Setting Has Been Deleted"], 200);
    }
}
