<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\User;
use App\Http\Resources\NotificationResource;
use App\Models\Driver;
use App\Events\DriverUserNotificationEvent;
use App\Http\Controllers\Controller;
use App\Events\PushNotificationEvent;
use App\Helpers\BackendValidation;

class DynamicNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $notifications = NotificationResource::collection(Notification::get());

        return response()->json(['status' => 'success','notifications' => $notifications],200);
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
        $validate = BackendValidation::sendNotification($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        if($request->user_id){

            $user = User::find($request->user_id);

            DriverUserNotificationEvent::dispatch($user,null,$request->title,$request->description,null,'mrautokaar');

        }elseif($request->driver_id){

            $driver = Driver::find($request->driver_id);

            DriverUserNotificationEvent::dispatch($driver,null,$request->title,$request->description,null,'mrautokaar');

        }else{

            PushNotificationEvent::dispatch($request);
        }

        return response()->json(['status' => 'success','message' => "The Notification Sent Successfully"],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $notify = Notification::find($id)->delete();

        return response()->json(['status' => 'success','message' => "The Notification is deleted"],200);
    }
}
