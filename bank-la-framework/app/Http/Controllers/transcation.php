<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;


class transcation extends Controller
{

    public function fetchTransactions(Request $request)
    {
        $userId = Auth::user()->id;
        $limit = (int) $request->get('y', 0);
        $lim = $limit + 3;

        $transactions = Transaction::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($lim)
            ->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'result' => 'empty',
                'message' => 'No transactions found.',
                'transactions' => [],
                'limit' => $lim,
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
                $pone = $transaction->sender_bank_detail;
                if (ctype_alpha($pone)) {
                    $message = "transferred from " . $pone;
                } else {
                    $user = User::where('phone_number', $pone)->first();
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
            'limit' => $lim,
        ]);
    }


    public function fetchTransactionSingle(Request $request)
    {
        $userId = Auth::user()->id;
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
            $pone = $transaction->sender_bank_detail;
            if (ctype_alpha($pone)) {
                $message = "transferred from " . $pone;
            } else {
                $user = User::where('phone_number', $pone)->first();
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
