<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GdprDeleteRequest;
use App\Models\User;
use App\Jobs\ProcessGdprDelete;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserGDPRDeleteController extends Controller
{
    // User requests deletion
    public function requestDelete(Request $request)
    {
        $user = $request->user();

        // Prevent duplicates
        if (GdprDeleteRequest::where('user_id', $user->id)
            ->where('status', 'pending')->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'You already have a pending deletion request.'
            ], 409);
        }

        $req = GdprDeleteRequest::create([
            'user_id' => $user->id,
            'reason'  => $request->reason,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delete request submitted and awaiting approval.'
        ]);
    }

    // Admin/owner approves request
    public function approve(Request $request, $id)
    {
        $acting = $request->user();
        $orgId  = $request->header('X-Org-Id');

        // Must have permission
        if (! $acting->hasPermission($orgId, 'users.delete')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $req = GdprDeleteRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Request already processed.'
            ], 409);
        }

        $req->update([
            'status' => 'approved',
            'approved_at' => now(),
            'acted_by' => $acting->id,
        ]);

        // queue deletion
        ProcessGdprDelete::dispatch($req->id);

        return response()->json([
            'status' => true,
            'message' => 'Deletion approved. User will be deleted shortly.'
        ]);
    }

    // Admin/owner rejects request
    public function reject(Request $request, $id)
    {
        $acting = $request->user();

        if (! $acting->hasPermission(null, 'users.delete')) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $req = GdprDeleteRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Request already processed.'
            ], 409);
        }

        $req->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'acted_by' => $acting->id,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Deletion request rejected.'
        ]);
    }
}
