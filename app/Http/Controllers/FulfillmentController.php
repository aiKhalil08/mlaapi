<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fulfillment;

class FulfillmentController extends Controller
{
    public function add(Request $request) {
        try {
            $student = auth()->user();

            $data = ['type'=>$request->type, 'amount'=>$request->payout_amount];
            
            if ($request->type == 'withdrawal') {
                $data['account_details'] = json_encode(['account_number'=>$request->account_number, 'account_name'=> $request->account_name, 'bank_name'=>$request->bank_name]);
            }


            $student->fulfillments()->create($data);


    
    
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }


    public function get_all(Request $request) {
        $data = Fulfillment::get_all();


        return response()->json($data, 200);
    }

    public function get(Request $request, int $id) {
        if (!$fulfillment = Fulfillment::get($id)) return response()->json(['status'=>'failed', 'message'=>'Invalid id'], 200);

        // $sale = $sale->load(['student:first_name,last_name,email', 'referral'=>'referrer:first_name,last_name,email']);

        return response()->json(['status'=>'success' ,'fulfillment'=>$fulfillment], 200);
    }

    public function fulfill(Request $request) {

        try {
            if (!$fulfillment = Fulfillment::find($request->id)) return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);

            $fulfillment->update(['status'=>$request->fulfillment, 'date_fulfilled'=>\Carbon\Carbon::now()]);

            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }
}
