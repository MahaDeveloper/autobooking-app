<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\BackendValidation;
use App\Http\Resources\ModuleResource;
use App\Http\Resources\RoleResource;
use App\Models\Module;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = RoleResource::collection(Role::all());

        return response()->json(['status' => 'success', 'roles' => $roles], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $modules = Module::with(['permissions' => function ($query) {
            $query->where('status', 1);
        }])->active()->get();
        return response()->json(['status' => 'success', 'modules' => $modules], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validate = BackendValidation::roleValidation($request,$id=null);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $role = new Role();
        $role->role = $request->role;
        $role->code = strtoupper($request->role);
        $role->save();
        $role->permissions()->sync($request->permissions);

        return response()->json(['status' => 'success', 'message' => "The Role Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = new RoleResource(Role::findOrFail($id));

        return response()->json(['status' => 'success', 'role' => $role], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $role = new RoleResource(Role::findOrFail($id));

        /* $modules = Module::with(['permissions' => function($query){
        $query->where('status',1);
        }])->active()->get();*/

        $modules = ModuleResource::collection(Module::active()->with('permissions')->get());

        $permissions = DB::table('permission_role')
            ->where('permission_role.role_id', $id)
            ->pluck('permission_role.permission_id');

        return response()->json(['status' => 'success', 'role' => $role, 'permissions' => $permissions, 'modules' => $modules], 200);
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
        $validate = BackendValidation::roleValidation($request,$id);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $role = Role::find($id);
        $role->role = $request->role;
        $role->code = strtoupper($request->role);
        $role->save();
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json(['status' => 'success', 'message' => "The Role Has Been Updated Successfully"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = Admin::where('role_id', $id)->get();

        $role = Role::find($id);

        if ($admin) {
            return response()->json(['status' => 'error', 'message' => "The Role canot be deleted,It is assigned to a admin"], 200);
        } else {
            $role->delete();

            return response()->json(['status' => 'success', 'message' => "The Role Has Been Deleted"], 200);
        }
    }

    public function status($id)
    {

        $role = Role::find($id);

        if ($role->status) {
            $role->status = 0;
            $msg = "The Role Has Been De Activated";
        } else {
            $role->status = 1;
            $msg = "The Role Has Been Activated";
        }

        $role->save();

        return response()->json(['status' => 'success', 'message' => $msg], 200);
    }

    public function activeRoles(){

        $roles = RoleResource::collection(Role::active()->get());

        return response()->json(['status' => 'success', 'roles' => $roles], 200);
    }
}
