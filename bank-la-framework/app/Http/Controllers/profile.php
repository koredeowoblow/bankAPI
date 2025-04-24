<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class profile extends controller
{

    public function getUserProfile(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'result' => 'success',
            'user' => [
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'account_balance' => $user->account_balance,
                'status' => $user->status,
                'loan_amount' => $user->loan_amount,
            ],
        ]);
    }

    public function updateUserProfile(Request $request)
    {
        $request->validate([
            'fullname' => 'string|max:255',
            'email' => 'email|max:255',
            'phone_number' => 'string|max:15',
        ]);
        $data=[];
        if ($request->fullname != null) {
            $data['fullname'] = $request->fullname;
        }
        if($request->email != null){
            $data['email'] = $request->email;
        }
        if($request->phone_number != null){
            $data['phone_number']=$request->phone_number;
        }

        User::where('id', $request->id)->update($data);

        return response()->json([
            'result' => 'success',
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function getaccountBalance(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'result' => 'success',
            'account_balance' => $user->account_balance,
        ]);
    }
}
