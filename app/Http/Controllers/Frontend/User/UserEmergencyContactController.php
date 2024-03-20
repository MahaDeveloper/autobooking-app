<?php

namespace App\Http\Controllers\Frontend\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\UserAppValidation;
use App\Models\UserEmergencyContact;
use App\Http\Resources\UserEmergencyContactResource;

class UserEmergencyContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();

        $user_emergency_contact = UserEmergencyContactResource::collection(UserEmergencyContact::where('user_id',$user->id)->get());

        return response()->json(['status' => 'success', 'user_emergency_contact' => $user_emergency_contact], 200);
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
        $validate = UserAppValidation::emergencyContactValidation($request,$id=null);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $user = auth()->user();

        foreach($request->contacts as $contact){

            $emergency_contact = new UserEmergencyContact();
            $emergency_contact->user_id = $user->id;
            $emergency_contact->mobile = $contact['mobile'];
            $emergency_contact->save();
        }
        return response()->json(['status' => 'success', 'message' => "The Emergency Contact Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $emergency_contact = new UserEmergencyContactResource(UserEmergencyContact::findOrFail($id));

        return response()->json(['status' => 'success', 'emergency_contact' => $emergency_contact], 200);
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
        foreach($request->contacts as $contact){

            $validate = UserAppValidation::emergencyContactValidation($request,$contact['id']);

            if ($validate['status'] == "error") {

                return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
            }

            $emergency_contact = UserEmergencyContact::find($contact['id']);
            $emergency_contact->mobile = $contact['mobile'];
            $emergency_contact->save();
        }

        return response()->json(['status' => 'success', 'message' => "The Emergency Contact Has Been Updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $emergency_contact = UserEmergencyContact::find($id);

        $emergency_contact->delete();

        return response()->json(['status' => 'success', 'message' => "The emergency user contact Has Been Deleted"], 200);
    }

}
