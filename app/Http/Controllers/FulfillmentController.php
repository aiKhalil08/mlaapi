<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fulfillment;
use Illuminate\Support\Facades\DB;

class FulfillmentController extends Controller
{
    public function add(Request $request) {
        try {
            $student = auth()->user();

            $data = ['type'=>$request->type, 'amount'=>$request->payout_amount];
            
            if ($request->type == 'withdrawal') {
                $data['account_details'] = ['account_number'=>$request->account_number, 'account_name'=> $request->account_name, 'bank_name'=>$request->bank_name];
            }


            $student->fulfillments()->create($data);


    
    
            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again'], 200);
        }
    }


    public function get_all(Request $request) {

        $pending = Fulfillment::pending()->select(['id', 'amount', 'date_added', 'type', 'user_id'])->with([
            'affiliate' => function ($query) {
                $query->select(['id', DB::raw('concat(first_name," ", last_name) as full_name')]);
            }
        ])->get();

        $history = Fulfillment::fulfilled()->orWhere->rejected()->select(['id', 'amount', 'date_fulfilled', 'status_id', 'type', 'user_id'])->with([
            'affiliate' => function ($query) {
                $query->select(['id', DB::raw('concat(first_name," ", last_name) as full_name')]);
            },
            'status:id,name',
        ])->get();

        $data = ['pending' => $pending, 'history'=>$history];


        return response()->json($data, 200);
    }

    public function get(Request $request, int $id) {
        $fulfillment = Fulfillment::select(['amount', 'date_added', 'type', 'account_details', 'user_id'])->with([
            'affiliate' => function ($query) {
                $query->select(['id', DB::raw('concat(first_name, " ", last_name) as full_name'), 'email']);
            }
        ])->find($id);


        if (!$fulfillment) return response()->json(['status'=>'failed', 'message'=>'Invalid id'], 200);


        return response()->json(['status'=>'success' ,'fulfillment'=>$fulfillment], 200);
    }

    public function fulfill(Request $request) {

        try {
            if (!$fulfillment = Fulfillment::find($request->id)) return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);

            $fulfillment->update(['status_id'=>$request->fulfillment, 'date_fulfilled'=>\Carbon\Carbon::now()]);

            return response()->json(['status'=>'success'], 200);
        } catch (\Throwable $th) {
            return response()->json(['status'=>'failed', 'message'=>'Something went wrong. Please try again.'], 200);
        }
    }
}
