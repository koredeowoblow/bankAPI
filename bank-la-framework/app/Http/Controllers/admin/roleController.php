<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AccessRole;
use App\Models\AccessModule;
use App\Models\Access;

class roleController extends Controller
{
    public function index()
    {
        $roles = AccessRole::all();
        return response()->json([
            'status' => 'success',
            'roles' => $roles,
        ]);
    }
    private function createAccessForRole($roleId)
    {
        $modules = AccessModule::all();
        $crudPermissions = [];

        foreach ($modules as $module) {
            // Format: [module_id, create, view, edit, delete]
            $crudPermissions[] = [$module->id, "0", "0", "0", "0"];
        }

        Access::create([
            'role_id' => $roleId,
            'crud' => json_encode($crudPermissions),
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
        ]);
        $role = AccessRole::create([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);
       $this->createAccessForRole($role->id);
        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'role' => $role,
        ]);
    }



    public function show(Request $request)
    {
        $role = AccessRole::find($request->id);
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'role' => $role,
        ]);
    }
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
        ]);
        $role = AccessRole::find($request->id);
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
            ], 404);
        }
        $role->update([
            'name' => $request->name,
            'slug' => $request->slug,
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully',
            'role' => $role,
        ]);
    }
    public function destroy(Request $request)
    {
        $role = AccessRole::find($request->id);
        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found',
            ], 404);
        }
        $role->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully',
        ]);
    }
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
        ]);
        $query = AccessRole::where(
            'name',
            'LIKE',
            '%' . $request->query . '%'
        )->orWhere(
            'slug',
            'LIKE',
            '%' . $request->query . '%'
        )->get();
        if ($query->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No roles found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'roles' => $query,
        ]);
    }
}
