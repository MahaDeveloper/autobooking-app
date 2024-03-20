<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Models\Support;
use App\Http\Resources\SupportResource;
use App\Http\Controllers\Controller;
use App\Interfaces\SupportInterface;
use App\Helpers\BackendValidation;

class SupportController extends Controller
{
    public function supportList(Request $request){

        $supports = SupportResource::collection(Support::with('supportable')->where('status',0)->get());

        return response()->json(['status' => 'success','supports' => $supports],200);
    }

    public function resolvedSupportList(Request $request){

        $supports = SupportResource::collection(Support::with('supportable')->where('status',1)->get());

        return response()->json(['status' => 'success','supports' => $supports],200);

    }

    public function replySupport(Request $request,SupportInterface $support){

        $validate = BackendValidation::replySupportValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $support->supportReply($request);

        return response()->json(['status' => 'success','message' => "The Resolved Successfully"],200);

    }


}
