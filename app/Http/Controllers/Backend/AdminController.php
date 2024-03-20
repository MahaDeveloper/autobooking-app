<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\BackendValidation;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admins = AdminResource::collection(Admin::all());

        return response()->json(['status' => 'success', 'admins' => $admins], 200);
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
        $validate = BackendValidation::adminValidation($request,$id=null);
        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $admin = new Admin;
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->password = $request->password;
        $admin->role_id = $request->role_id;
        $admin->mobile = $request->mobile;
        $admin->email = $request->email;
        $admin->save();

        return response()->json(['status' => 'success', 'message' => "The Admin Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = new AdminResource(Admin::findOrFail($id));

        return response()->json(['status' => 'success', 'admin' => $admin], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = new AdminResource(Admin::findOrFail($id));

        return response()->json(['status' => 'success', 'admin' => $admin], 200);
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
        $validate = BackendValidation::adminValidation($request,$id);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $admin = Admin::find($id);
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->email = $request->email;
        $admin->mobile = $request->mobile;
        if ($request->has('role_id')) {
            $admin->role_id = $request->role_id;
        }

        if ($request->has('password')) {
            $admin->password = $request->password;
        }
        $admin->save();

        return response()->json(['status' => 'success', 'message' => "The Admin Has Been Updated Successfully"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = Admin::find($id);

        $admin->delete();

        return response()->json(['status' => 'success', 'message' => "The Admin Has Been Deleted"], 200);
    }

    public function status($id)
    {
        $admin = Admin::find($id);

        if ($admin->status) {
            $admin->status = 0;
            $msg = "The Admin Has Been De Activated";
        } else {
            $admin->status = 1;
            $msg = "The Admin Has Been Activated";
        }

        $admin->save();

        return response()->json(['status' => 'success', 'message' => $msg], 200);
    }
}
