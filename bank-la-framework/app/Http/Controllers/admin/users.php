<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class users extends Controller
{
    /**
     * Admin-only access check
     */
    private function authorizeAdmin()
    {
        if (Auth::user()->role_id != 1) {
            abort(response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 403));
        }
    }

    public function index()
    {
        $users = User::all();
        return response()->json([
            'status' => 'success',
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname'      => 'required|string|max:255',
            'email'         => 'required|email|unique:users',
            'phone_number'  => 'required|string|max:20|unique:users',
            'password'      => 'required|string|min:6|confirmed',
            'pin'           => 'required|digits:4',
        ]);

        $user = User::create([
            'fullname'        => $request->fullname,
            'email'           => $request->email,
            'phone_number'    => $request->phone_number,
            'password'        => Hash::make($request->password),
            'role_id'         => 2,
            'account_balance' => 0.00,
            'pin'             => Hash::make($request->pin),
            'status'          => 'active',
            'loan_amount'     => 0.00,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully',
            'user'    => $user,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'fullname'      => 'string|max:255',
            'email'         => 'email|max:255|unique:users,email,' . $id,
            'phone_number'  => 'string|max:20|unique:users,phone_number,' . $id,
            'password'      => 'nullable|string|min:6|confirmed',
            'pin'           => 'nullable|digits:4',
        ]);

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->filled('pin')) {
            $user->pin = Hash::make($request->pin);
        }

        $user->fill($request->except(['password', 'pin']))->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully',
            'user'    => $user,
        ], 200);
    }

    public function findSingle(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($request->id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'user'   => $user,
        ], 200);
    }
    public function fetchActive()
    {
        $this->authorizeAdmin();

        $users = User::where('status', 'active')->get();

        return response()->json([
            'status' => 'success',
            'users'  => $users,
        ], 200);
    }

    /**
     * Fetch inactive users
     */
    public function fetchInactive()
    {
        $this->authorizeAdmin();

        $users = User::where('status', 'disable')->get();

        return response()->json([
            'status' => 'success',
            'users'  => $users,
        ], 200);
    }

    public function disable(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($request->id);
        $user->update(['status' => 'disable']);

        return response()->json([
            'status'  => 'success',
            'message' => 'User disabled successfully',
        ], 200);
    }

    public function enable(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);

        $user = User::find($request->id);
        $user->update(['status' => 'active']);

        return response()->json([
            'status'  => 'success',
            'message' => 'User enabled successfully',
        ], 200);
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User deleted successfully',
        ], 200);
    }

    public function search(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $users = User::where('fullname', 'LIKE', '%' . $request->query . '%')
            ->orWhere('email', 'LIKE', '%' . $request->query . '%')
            ->orWhere('phone_number', 'LIKE', '%' . $request->query . '%')
            ->get();

        return response()->json([
            'status' => 'success',
            'users'  => $users,
        ], 200);
    }
}
