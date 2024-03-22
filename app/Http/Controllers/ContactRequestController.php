<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactRequest;
use Illuminate\Support\Facades\DB;

class ContactRequestController extends Controller
{
    public function store(Request $request) {
        $_request = null;
        DB::transaction(function () use ($request, &$_request) {
            $_request = ContactRequest::create($request->only(['first_name', 'last_name', 'phone_number', 'email_address', 'message']));
        });
        return $_request;
    }

    public function get_list(Request $request, string $count) {
        $attributes = ['first_name', 'last_name', DB::raw('substring(message, 1, 75) as sub_message'), 'created_at', 'viewed'];
        if ($count != 'all') {
            $_requests = ContactRequest::select($attributes)->orderBy('id', 'desc')->take($count)->get();
        } else {
            $_requests = ContactRequest::select($attributes)->orderBy('id', 'desc')->get();
        }
        return response()->json($_requests, 200);
    }

    public function get(Request $request, string $last_name, string $created_at) {
        $_request = ContactRequest::where(['last_name'=> $last_name, 'created_at'=>$created_at])->first();
        $_request->viewed = 1;
        $_request->save();
        return response()->json($_request, 200);
    }
}
