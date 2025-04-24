<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class staffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Auth::guard("admin")->logout();
        $staff = Staff::all();
        return response()->json([
            'status' => 'success',
            'staff' => $staff,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(
            [
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|unique:staff',
                'phone_number' => 'required|string|max:20|unique:staff',
                'password' => 'required|string|min:6|confirmed',
                'role_id' => 'required|integer',
            ]
        );
        $staff = Staff::create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Staff created successfully',
            'staff' => $staff,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        Auth::guard("admin")->logout();
        $staff = Staff::find($request->id);
        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'staff' => $staff,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        Auth::staff();
        $request->validate([
            'fullname' => 'string|max:255',
            'email' => 'email|max:255|unique:staff,email,' . $staff->id,
            'phone_number' => 'string|max:20|unique:staff,phone_number,' . $staff->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role_id' => 'integer',
        ]);
        $staff->update($request->all());
        return response()->json([
            'status' => 'success',
            'message' => 'Staff updated successfully',
            'staff' => $staff,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        Auth::staff();
        $request->validate([
            'id'=>"required|integer|exists:staff,id",
        ]);
        $staff = Staff::find($request->id);
        if (!$staff) {
            return response()->json([
                'status' => 'error',
                'message' => 'Staff not found',
            ], 404);
        }
        $staff->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Staff deleted successfully',
        ]);
    }
}
