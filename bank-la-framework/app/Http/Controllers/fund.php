<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;

class fund extends Controller
{
    public function initializePayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
        ]);

        $user = Auth::user();

        return response()->json([
            'result' => 'success',
            'email' => $user->email,
            'amount' => $request->amount,
            'publicKey' => env('PAYSTACK_PUBLIC_KEY'),
        ]);
    }

    public function verifyPayment(Request $request)
    {
        $request->validate([
            'reference' => 'required|string'
        ]);

        $reference = $request->reference;
        $secretKey = env('PAYSTACK_SECRET_KEY');

        $response = Http::withToken($secretKey)
            ->get("https://api.paystack.co/transaction/verify/{$reference}");

        if (!$response->ok()) {
            return response()->json([
                'result' => 'fail',
                'error' => 'Paystack API error.'
            ], 500);
        }

        $data = $response->object()->data;

        if ($data->status !== 'success') {
            return response()->json([
                'result' => 'fail',
                'error' => 'Payment was not successful.'
            ]);
        }

        $user = Auth::user();
        $amount = $data->amount / 100; // Convert kobo to Naira
        $sendingDetails = $data->authorization->bank ?? 'Unknown Bank';
        $fundingDetails = json_encode([
            'channel' => $data->authorization->channel ?? '',
            'card_type' => $data->authorization->card_type ?? '',
            'last4' => $data->authorization->last4 ?? '',
        ]);
        $recipientBankDetails = json_encode([
            'account_number' => $user->phone_number,
            'account_name' => $user->fullname,
            'bank_name' => 'clarity'
        ]);

        // Record transaction
        DB::beginTransaction();
        try {
            Transaction::create([
                'reference_number' => $reference,
                'transaction_type' => 'funding',
                'amount' => $amount,
                'sender_bank_detail' => $sendingDetails,
                'recipient_bank_details' => $recipientBankDetails,
                'user_id' => $user->id,
                'funding_details' => $fundingDetails,
                'transaction_nature' => 'credit',
                'created_at' => now(),
            ]);

            // Update user balance
            $user->account_balance += $amount;
            $user->save();

            DB::commit();

            return response()->json(['result' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'result' => 'fail',
                'error' => 'Transaction processing failed.',
                'debug' => $e->getMessage()
            ], 500);
        }
    }
}
