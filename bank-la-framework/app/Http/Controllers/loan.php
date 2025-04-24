<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\LoanData;

class loan extends Controller{
    public function createLoan(Request $request)
    {
        $request->validate([
            'principal' => 'required|numeric|min:1',
            'fixedInterestRate' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'durationType' => 'required|string|in:days,months,years',
            'nextOfKin' => 'required|string',
            'nextOfKinPhone' => 'required|string',
        ]);

        $user = Auth::user();
        $user_id=$user->id;

        $principal = $request->input('principal');
        $fixedInterestRate = $request->input('fixedInterestRate');
        $duration = $request->input('duration');
        $durationType = $request->input('durationType');

        $nextOfKin = $request->input('nextOfKin');
        $nextOfKinPhone = $request->input('nextOfKinPhone');



        $interest = $principal * $fixedInterestRate * $duration;
        $totalAmount = $principal + $interest;

        // Check number of active loans
        $existingLoansCount = LoanData::where('user_id', $user_id)->count();

        if ($existingLoansCount >= 3) {
            return response()->json(['status' => 'excess']);
        }

        // Create loan
        try {
            LoanData::create([
                'principal' => $principal,
                'user_id' => $user_id,
                'fixed_interest_rate' => $fixedInterestRate,
                'duration' => $duration,
                'duration_type' => $durationType,
                'next_of_kin' => $nextOfKin,
                'next_of_kin_phone' => $nextOfKinPhone,
                'interest' => $interest,
                'total_amount' => $totalAmount
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
    public function fetchLoanDetails(Request $request)
    {
        $loanId = $request->input('id');
        $user = Auth::user();
        $user_id=$user->id;
        $loan = LoanData::where('id', $loanId)
            ->where('user_id', $user_id)
            ->first();

        if (!$loan) {
            return response()->json(['status' => 'failed']);
        }

        $amount = number_format($loan->total_amount, 2);
        $status = $loan->status === 1 ? 'active' : 'pending';

        return response()->json([
            'status' => 'success',
            'loan' => [
                'id' => $loan->id,
                'principal' => number_format($loan->principal, 2),
                'interest' => number_format($loan->interest, 2),
                'total_amount' => '₦' . $amount,
                'duration' => $loan->duration . " " . ucfirst($loan->duration_type),
                'next_of_kin' => $loan->next_of_kin,
                'next_of_kin_phone' => $loan->next_of_kin_phone,
                'status' => $status,
                'created_at' => $loan->created_at->toDateTimeString(),
            ],
        ]);
    }
    public function fetchLoans(Request $request)
    {
        $user_id = Auth::user()->id;
        $limit = (int) $request->get('y', 0);
        $lim = $limit + 3;

        $loans = LoanData::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->limit($lim)
            ->get();

        if ($loans->isEmpty()) {
            return response()->json([
                'result' => 'empty',
                'message' => 'No loans found.',
                'loans' => [],
                'limit' => $lim,
            ]);
        }

        $data = [];

        foreach ($loans as $loan) {
            $amount = number_format($loan->total_amount, 2);
            $status = $loan->status === 1 ? 'active' : 'inactive';

            $data[] = [
                'id' => $loan->id,
                'principal' => number_format($loan->principal, 2),
                'interest' => number_format($loan->interest, 2),
                'total_amount' => '₦' . $amount,
                'duration' => $loan->duration . " " . ucfirst($loan->duration_type),
                'next_of_kin' => $loan->next_of_kin,
                'next_of_kin_phone' => $loan->next_of_kin_phone,
                'status' => $status,
                'created_at' => $loan->created_at->toDateTimeString(),
            ];
        }

        return response()->json([
            'result' => 'success',
            'loans' => $data,
            'limit' => $lim,
        ]);
    }
    public function deleteLoan(Request $request)
    {
        $loanId = $request->input('id');
        $user = Auth::user();
        $user_id = $user->id;

        $loan = LoanData::where('id', $loanId)
            ->where('user_id', $user_id)
            ->first();

        if (!$loan) {
            return response()->json(['status' => 'failed']);
        }

        try {
            $loan->delete();
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
    public function updateLoan(Request $request)
    {
        $loanId = $request->input('id');
        $user = Auth::user();
        $user_id=$user->id;

        $loan = LoanData::where('id', $loanId)
            ->where('user_id', $user_id)
            ->first();

        if (!$loan) {
            return response()->json(['status' => 'failed']);
        }

        try {
            $loan->update($request->all());
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'error' => $e->getMessage()]);
        }
    }
}
