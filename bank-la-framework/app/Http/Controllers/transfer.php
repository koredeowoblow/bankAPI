<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Bank;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;

class transfer extends Controller
{
    //
    public function sameBankTransfer(Request $request)
    {
        $request->validate([
            'account_numbered' => 'required|numeric|exists:users,phone_number',
            'amounted'         => 'required|numeric',
            'pin'              => 'required|string|min:4',
        ]);

        $user = Auth::user();

        if (!$user || !Hash::check($request->pin, $user->pin)) {
            return response()->json(['message' => 'Invalid PIN'], 403);
        }

        if ($user->account_balance < $request->amounted) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        DB::beginTransaction();
        try {
            $recipient = User::where('phone_number', $request->account_numbered)->first();
            $user = User::where('id', $user->id)->first();

            if (!$recipient) {
                return response()->json(['message' => 'Recipient not found'], 404);
            }

            // Adjust balances
            $user->account_balance -= $request->amounted;
            $user->save();

            $recipient->account_balance += $request->amounted;
            $recipient->save();

            // Record transaction
            $reference = uniqid('ref_');
            $timestamp = now();
            $bankDetails = json_encode([
                'account_name'   => $recipient->name,
                'account_number' => $recipient->phone_number,
                'bank'  => 'clarity'
            ]);

            Transaction::create([
                'reference_number'       => $reference,
                'transaction_type'       => 'same_bank_transfer',
                'amount'                 => $request->amounted,
                'user_id'                => $user->id,
                'sender_bank_detail'     => $user->phone_number,
                'recipient_bank_details' => $bankDetails,
                'created_at'             => $timestamp,
                'transaction_nature'     => 'debit'
            ]);

            Transaction::create([
                'reference_number'       => $reference,
                'transaction_type'       => 'same_bank_transfer',
                'amount'                 => $request->amounted,
                'user_id'                => $recipient->id,
                'sender_bank_detail'     => $user->phone_number,
                'recipient_bank_details' => $bankDetails,
                'created_at'             => $timestamp,
                'transaction_nature'     => 'credit'
            ]);

            DB::commit();
            return response()->json([
                'result' => 'success',
                'message' => 'Transactions fetched successfully.',

            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 'failed', 'error' => $e->getMessage()], 500);
        }
    }
    public function checkAmount(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        $user = auth()->user();

        if ($user->account_balance < $request->amount) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance'], 400);
        }

        return response()->json(['status' => 'success', 'message' => 'Sufficient balance'], 200);
    }

    public function Findaccount(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|exists:users,phone_number',
        ]);

        $user = User::where('phone_number', $request->account_number)->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Account not found'], 400);
        }

        return response()->json([
            'status' => 'success',
            'user'   => [
                'fullname' => $user->fullname,
                'phone_number' => $user->phone_number,
            ],
        ]);
    }


    public function resolveAccount(Request $request)
    {
        $bankCode = $request->input('bankCode');
        $acct_number = $request->input('acct_number');

        if (empty($bankCode) || empty($acct_number)) {
            return response()->json([
                'result' => 'fail',
                'error' => 'Invalid input parameters.'
            ], 400);
        }

        $secretKey = env('PAYSTACK_SECRET_KEY'); // retrieved from .env

        $response = Http::withToken($secretKey)->get('https://api.paystack.co/bank/resolve', [
            'account_number' => $acct_number,
            'bank_code' => $bankCode,
        ]);

        if ($response->failed()) {
            return response()->json([
                'result' => 'fail',
                'error' => 'API Error: ' . $response->body()
            ], $response->status());
        }

        return response()->json([
            'result' => 'success',
            'data' => $response->json()
        ]);
    }

    public function createTransferRecipient(Request $request)
    {
        // Get inputs from the request
        $bankCode = $request->input('bankCode');
        $acct_number = $request->input('acct_number');
        $acct_name = $request->input('acct_name');

        // Validate input
        if (empty($bankCode) || empty($acct_number) || empty($acct_name)) {
            return response()->json([
                'result' => 'fail',
                'error' => 'Invalid input parameters.'
            ], 400);
        }

        // Get the Paystack Secret Key from .env file
        $secretKey = env('PAYSTACK_SECRET_KEY');

        // Paystack API endpoint
        $url = 'https://api.paystack.co/transferrecipient';

        // Data to send in the request
        $data = [
            'type' => 'nuban',
            'name' => $acct_name,
            'account_number' => $acct_number,
            'bank_code' => $bankCode,
            'currency' => 'NGN',
        ];

        // Make the request to Paystack
        $response = Http::withToken($secretKey)
            ->asForm()
            ->post($url, $data);

        // Check if the request was successful
        if ($response->failed()) {
            return response()->json([
                'result' => 'fail',
                'error' => 'API Error: ' . $response->body(),
            ], $response->status());
        }

        // Return the successful response with data
        return response()->json([
            'result' => 'success',
            'data' => $response->json(),
        ]);
    }
    public function createTransfer(Request $request)
    {
        // Validate input fields
        $request->validate([
            'pin' => 'required',
            'amount' => 'required|numeric',
            'recipient_code' => 'required',
            'acct_number' => 'required',
            'acct_name' => 'required',
            'bank_code' => 'required'
        ]);

        // Get the Paystack secret key
        $secretKey = env('PAYSTACK_SECRET_KEY');

        // Get authenticated user
        $user = Auth::user();

        // Verify PIN
        if (!Hash::check($request->input('pin'), $user->pin)) {
            return response()->json(['result' => 'fail', 'error' => 'Invalid PIN']);
        }

        $amount = $request->input('amount');
        $recipientCode = $request->input('recipient_code');
        $acctNumber = $request->input('acct_number');
        $acctName = $request->input('acct_name');
        $bankCode = $request->input('bank_code');
        $reason = $request->input('reason');

        // Check if user has sufficient balance
        if ($user->account_balance < $amount) {
            return response()->json(['result' => 'fail', 'error' => 'Insufficient balance']);
        }

        // Retrieve bank name
        $bank = Bank::where('bank_code', $bankCode)->first();
        if (!$bank) {
            return response()->json(['result' => 'fail', 'error' => 'Invalid bank code']);
        }
        $bankName = $bank->bank_name;

        // Generate a unique reference
        $uniqueReference = uniqid('ref_');

        // Prepare data for Paystack API
        $fields = [
            'source' => 'balance',
            'recipient' => $recipientCode,
            'reason' => $reason,
            'amount' => $amount * 100, // Convert to kobo
            'reference' => $uniqueReference
        ];

        // Send API request to Paystack
        $response = Http::withToken($secretKey)
            ->asForm()
            ->post('https://api.paystack.co/transfer', $fields);

        if ($response->failed()) {
            return response()->json(['result' => 'fail', 'error' => 'Transfer failed: ' . $response->body()]);
        }

        $paystackData = $response->json();
        if (!$paystackData['status']) {
            return response()->json(['result' => 'fail', 'error' => 'Paystack error: ' . $paystackData['message']]);
        }

        // Use DB transaction to keep data consistent
        DB::beginTransaction();
        try {
            // Update user balance
            $user->account_balance -= $amount;
            $user->save();

            // Record the transaction
            $transactionData = [
                'reference_number' => $uniqueReference,
                'transaction_type' => 'bank_transfer',
                'amount' => $amount,
                'user_id' => $user->id,
                'sender_bank_detail' => $user->phone_number,
                'recipient_bank_details' => json_encode([
                    'account_number' => $acctNumber,
                    'account_name' => $acctName,
                    'bank_name' => $bankName
                ]),
                'transaction_nature' => 'debit',
                'created_at' => now(),
                'updated_at' => now()
            ];

            Transaction::create($transactionData);

            DB::commit();

            return response()->json(['result' => 'success', 'message' => 'Transfer successful']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['result' => 'fail', 'error' => 'Internal error: ' . $e->getMessage()]);
        }
    }
}
