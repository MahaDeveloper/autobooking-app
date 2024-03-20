<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Models\OtherCharge;
use App\Helpers\BackendValidation;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $prices = PriceResource::collection(Price::all());

        return response()->json(['status' => 'success', 'prices' => $prices], 200);
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
        $validate = BackendValidation::priceValidation($request);

        if ($validate['status'] == "error") {
            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }

        $other_price = OtherCharge::where('type',1)->first();//min km

        $price = Price::latest()->first();

        if($request->to == null){

            $price = new Price;
            $price->from = $request->from;
            $price->to = $request->to;
            $price->amount = $request->amount;
            $price->save();

            return response()->json(['status' => 'success', 'message' => 'The Highest Distance Fare You Given!,There is No Available To Add Another Distance Fare'], 200);
        }

        if($request->from >= 25)
            return response()->json(['status' => 'error', 'message' => 'From Km should be Within 25 km'], 400);

        if($request->to > 25)
            return response()->json(['status' => 'error', 'message' => 'To Km should Not Exceed 25 km'], 400);

        if(!$price){
            if($other_price->min_km_time != $request->from){

                return response()->json(['status' => 'error', 'message' => 'From Km should be in '.$other_price->min_km_time.' km '], 400);
            }
        }else{
            if($price->to != $request->from){

                return response()->json(['status' => 'error', 'message' => 'From Km should be in '.$price->to.' km'], 400);
            }
        }

        $price = new Price;
        $price->from = $request->from;
        $price->to = $request->to;
        $price->amount = $request->amount;
        $price->save();

        return response()->json(['status' => 'success', 'message' => "The Price Has Been Saved"], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $price = new PriceResource(Price::findOrFail($id));

        return response()->json(['status' => 'success', 'price' => $price], 200);
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
        $validate = BackendValidation::priceValidation($request);

        if ($validate['status'] == "error") {

            return response()->json(['status' => 'error', 'message' => $validate['message']], 400);
        }
        $previous_price = Price::where('id','<',$id)->orderBy('from')->first();

        $other_price = OtherCharge::where('type',1)->first();//min km

        $price = Price::latest()->first();

        if($request->from >= 25)
            return response()->json(['status' => 'error', 'message' => 'From Km should be Within 25 km'], 400);

        if($request->to > 25)
            return response()->json(['status' => 'error', 'message' => 'To Km should Not Exceed 25 km'], 400);

        if(!$previous_price){

            if($other_price->min_km_time != $request->from)

                return response()->json(['status' => 'error', 'message' => 'From Km should be in '.$other_price->min_km_time.' km '], 400);

        }else{
            if($previous_price->to != $request->from)

             return response()->json(['status' => 'error', 'message' => 'From Km should be in '.$previous_price->to.' km'], 400);
        }
        $price = Price::find($id);
        $price->from = $request->from;
        $price->to = $request->to;
        $price->amount = $request->amount;
        $price->save();

        return response()->json(['status' => 'success', 'message' => "The Price Has Been updated"], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $price = Price::find($id);

        $price->delete();

        return response()->json(['status' => 'success', 'message' => "The Price Has Been Deleted"], 200);
    }


}
