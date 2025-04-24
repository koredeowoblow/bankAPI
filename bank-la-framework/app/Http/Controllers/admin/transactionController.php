<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\models\User;
use Illuminate\Support\Facades\Auth;

class transactionController extends Controller
{
    public function fetchTransactions(Request $request)
    {
        $userId = Auth::user();
        $limit = (int) $request->get('y', 0);
        $limt = $limit + 3;

        $transactions = Transaction::orderBy('created_at', 'desc')
            ->limit($limt)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'result' => 'empty',
                'message' => 'No transactions found.',
                'transactions' => [],
                'limit' => $limt,
            ]);
        }

        $data = [];

        foreach ($transactions as $transaction) {
            $transactionNature = $transaction->transaction_nature;
            $amount = number_format($transaction->amount, 2);
            $sign = $transactionNature === 'credit' ? '+' : '-';

            $recipientDetails = json_decode($transaction->recipient_bank_details, true);
            $r_name = is_array($recipientDetails) ? $recipientDetails['account_name'] : $transaction->recipient_bank_details;

            if ($transactionNature === 'credit') {
                $phone = $transaction->sender_bank_detail;
                if (ctype_alpha($phone)) {
                    $message = "transferred from " . $phone;
                } else {
                    $user = User::where('phone_number', $phone)->first();
                    $name = $user ? $user->fullname : 'Unknown';
                    $message = "transferred from " . $name;
                }
            } else {
                $message = "transferred to " . $r_name;
            }

            $data[] = [
                'id' => $transaction->id,
                'type' => $transactionNature,
                'amount' => $sign . '₦' . $amount,
                'message' => $message,
                'created_at' => $transaction->created_at->toDateTimeString(),
            ];
        }

        return response()->json([
            'result' => 'success',
            'message' => 'Transactions fetched successfully.',
            'transactions' => $data,
            'limit' => $limt,
        ]);
    }


    public function fetchTransactionSingle(Request $request)
    {
        Auth::user();
        $request->validate([
            'id' => 'required|integer',
        ]);
        $transactionId = $request->get('id');

        $transaction = Transaction::find($transactionId);

        if (!$transaction) {
            return response()->json(['error' => 'Transaction not found.'], 404);
        }

        $transactionNature = $transaction->transaction_nature;
        $amount = number_format($transaction->amount, 2);
        $sign = $transactionNature === 'credit' ? '+' : '-';

        $recipientDetails = json_decode($transaction->recipient_bank_details, true);
        $r_name = is_array($recipientDetails) ? $recipientDetails['account_name'] : $transaction->recipient_bank_details;

        if ($transactionNature === 'credit') {
            $phone = $transaction->sender_bank_detail;
            if (ctype_alpha($phone)) {
                $message = "transferred from " . $phone;
            } else {
                $user = User::where('phone_number', $phone)->first();
                $name = $user ? $user->fullname : 'Unknown';
                $message = "transferred from " . $name;
            }
        } else {
            $message = "transferred to " . $r_name;
        }

        return response()->json([
            'result' => 'success',
            'transaction' => [
                'id' => $transaction->id,
                'type' => $transactionNature,
                'amount' => $sign . '₦' . $amount,
                'message' => $message,
                'created_at' => $transaction->created_at->toDateTimeString(),
            ],
        ]);
    }
}
