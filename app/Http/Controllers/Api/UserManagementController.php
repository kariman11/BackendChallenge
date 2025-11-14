<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    /**
     * Soft delete a user
     */
    public function destroy(Request $request, $id)
    {
        $acting = $request->user();
        $orgId  = $request->header('X-Org-Id');
        // Owner/admin only
        if (! $acting->hasPermission($orgId, 'users.delete')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        // Soft delete
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User soft deleted'
        ]);
    }

    /**
     * Restore a soft-deleted user
     */
    public function restore(Request $request, $id)
    {
        $acting = $request->user();
        $orgId  = $request->header('X-Org-Id');

        if (! $acting->hasPermission($orgId, 'users.update')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $user = User::withTrashed()->find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }

        if (! $user->trashed()) {
            return response()->json(['status' => false, 'message' => 'User is not deleted'], 400);
        }

        $user->restore();

        return response()->json([
            'status' => true,
            'message' => 'User restored successfully'
        ]);
    }
}
