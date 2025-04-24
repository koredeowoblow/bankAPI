<?php

namespace App\Http\Controllers;

use App\Models\Access;
use App\Models\AccessRole;
use App\Models\AccessModule;
use Illuminate\Http\Request;

class AccessController extends Controller
{
    // Show the permission form for a role
    // public function edit($role_id)
    // {
    //     $role = AccessRole::findOrFail($role_id);
    //     $modules = AccessModule::all();

    //     // Get or create default CRUD array for each module
    //     $accessData = [];
    //     foreach ($modules as $module) {
    //         $access = Access::where('role_id', $role_id)
    //             ->where('access_module_id', $module->id)
    //             ->first();

    //         $accessData[$module->id] = $access->crud[$module->id] ?? [0, 0, 0, 0]; // default to [0,0,0,0]
    //     }

    //     // return view('access.edit', compact('role', 'modules', 'accessData'));
    // }

    // Update permission for a role
    public function updateAccessPermission(Request $request)
    {
        $request->validate([
            'permission_index' => 'required|integer|between:1,4', // 1=create, 2=view, 3=edit, 4=delete
            'value' => 'required|integar', // new value: "1" (yes), "0" (no)
            'module_id' => 'required|integer|exists:access_modules,id', // module being modified
            'role_id' => 'required|integer|exists:access_roles,id', // whose role's permission to update
        ]);
        // Extract meaningful variable names from request
        $permissionIndex = $request->input('permission_index'); // 1=create, 2=view, 3=edit, 4=delete
        $newPermissionValue = $request->input('value');         // new value: "1" (yes), "0" (no)
        $moduleId = $request->input('module_id');               // module being modified
        $roleId = $request->input('role_id');                   // whose role's permission to update

        // Fetch access record for the role
        $accessRecord = Access::where('role_id', $roleId)->first();

        if ($accessRecord) {
            // Decode the CRUD permissions
            $permissions = json_decode($accessRecord->crud);

            // Find the correct module and update the specified permission
            foreach ($permissions as $index => $modulePermissions) {
                if ($modulePermissions[0] == $moduleId) {
                    $permissions[$index][$permissionIndex] = $newPermissionValue;
                    break;
                }
            }

            // Save the updated permissions
            $accessRecord->crud = json_encode($permissions);
            $accessRecord->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Permission updated successfully.',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Access record not found for the specified role.',
        ]);
    }
}
