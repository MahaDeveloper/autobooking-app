<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Http\Resources\DriverResource;
use App\Models\Driver;

class RefferalController extends Controller
{
    public function refferalList(Request $request){

        $users = UserResource::collection(User::has('refferals')->with('refferals')->get());

        $drivers = DriverResource::collection(Driver::has('refferals')->with('refferals')->get());

        return response()->json(['status' => 'success','users' => $users,'drivers' => $drivers],200);
    }

    public function userRefferals($id){

        $users = UserResource::collection(User::where('refferal_id',$id)->with('refferer')->get());

        return response()->json(['status' => 'success','users' => $users],200);
    }

    public function driverRefferals($id){

        $drivers = DriverResource::collection(User::where('refferal_id',$id)->with('refferer')->get());

        return response()->json(['status' => 'success','drivers' => $drivers],200);
    }
}
