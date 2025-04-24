<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LoanData;
use Illuminate\Support\Facades\Auth;

class loancontroller extends Controller
{
    public function index()
    {
        $loans = LoanData::all();
        return response()->json([
            'status' => 'success',
            'loans' => $loans,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'principal' => 'required|numeric|min:1',
            'fixedInterestRate' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'durationType' => 'required|string|in:days,months,years',
            'nextOfKin' => 'required|string',
            'nextOfKinPhone' => 'required|string',
        ]);
        $loan = LoanData::create([$request->all()]);
        return response()->json([
            'status' => 'success',
            'message' => 'Loan created successfully',
            'loan' => $loan,
        ], 200);
    }
    public function show(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $loan = LoanData::find($id);
        if (!$loan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loan not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'loan' => $loan,
        ]);
    }
    public function update(Request $request)
    {
        Auth::user();
        $request->validate([
            'id' => 'required|integer',
            'principal' => 'numeric|min:1',
            'fixedInterestRate' => 'numeric|min:0',
            'duration' => 'integer|min:1',
            'durationType' => 'string|in:days,months,years',
            'nextOfKin' => 'string',
            'nextOfKinPhone' => 'string',
        ]);
        $loan = LoanData::find($request->id);
        if (!$loan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loan not found',
            ], 404);
        }
        $loan->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Loan updated successfully',
            'loan' => $loan,
        ]);
    }
    public function destroy($id)
    {
        $loan = LoanData::find($id);
        if (!$loan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loan not found',
            ], 404);
        }
        $loan->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Loan deleted successfully',
        ]);
    }
    public function ApprovedLoan(Request $request){
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $loan = LoanData::find($id);
        if (!$loan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loan not found',
            ], 404);
        }
        $loan->update([
            'status' => 'approved',
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Loan approved successfully',
            'loan' => $loan,
        ]);
    }
    public function RejectedLoan(Request $request){
        $request->validate([
            'id' => 'required|integer',
        ]);
        $id = $request->input('id');
        $loan = LoanData::find($id);
        if (!$loan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Loan not found',
            ], 404);
        }
        $loan->update([
            'status' => 'rejected',
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Loan rejected successfully',
            'loan' => $loan,
        ]);
    }
        public function fetchLoanByUser(Request $request){
        $request->validate([
            'user_id' => 'required|integer',
        ]);
        $user_id = $request->input('user_id');
        $loans = LoanData::where('user_id', $user_id)->get();
        if ($loans->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No loans found for this user',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'loans' => $loans,
        ]);
    }
    public function fetchLoanByStatus(Request $request){
        $request->validate([
            'status' => 'required|string|in:approved,rejected,pending',
        ]);
        $status = $request->input('status');
        $loans = LoanData::where('status', $status)->get();
        if ($loans->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No loans found with this status',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'loans' => $loans,
        ]);
    }
    public function fetchLoanByDate(Request $request){
        $request->validate([
            'date' => 'required|date',
        ]);
        $date = $request->input('date');
        $loans = LoanData::whereDate('created_at', $date)->get();
        if ($loans->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No loans found for this date',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'loans' => $loans,
        ]);
    }
}
