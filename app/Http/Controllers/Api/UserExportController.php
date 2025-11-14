<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ExportUserData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UserExportController extends Controller
{
    public function export(Request $request, $id)
    {
        $acting = $request->user();
        $orgId  = $request->header('X-Org-Id');
        // Only owner/admin can export users
        if (! $acting->hasPermission($orgId, 'users.read')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        $user = User::findOrFail($id);
        // Dispatch job
        ExportUserData::dispatch($user->id);

        return response()->json([
            'status' => true,
            'message' => 'Export started. You will receive an email once it is ready.'
        ]);
    }

    public function download(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (! $user->gdpr_export_zip || ! $user->gdpr_export_ready_at) {
            return response()->json([
                'status' => false,
                'message' => 'Export not ready'
            ], 404);
        }

        return response()->download(
            storage_path("app/{$user->gdpr_export_zip}")
        );
    }
}
