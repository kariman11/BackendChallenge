<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RsqlFilter;
use Illuminate\Http\Request;

class UserListController extends Controller
{
    public function index(Request $request)
    {
        $orgId = $request->header('X-Org-Id'); // optional, scope to org
        $perPage = min(100, intval($request->query('per_page', 20)));
        $cursor = $request->query('cursor');
        $fields = $request->query('fields');   // sparse fields
        $include = $request->query('include'); // relations
        $filter = $request->query('filter');   // RSQL logic

        // Base query
        $q = User::query();

        // Scope by organization
        if ($orgId) {
            $q->whereHas('organizations', fn($r) => $r->where('organizations.id', $orgId));
        }

        // Apply RSQL filter
        if ($filter) {
            RsqlFilter::apply($q, $filter);
        }

        // Always sorted for cursor pagination
        $q->orderBy('id', 'asc');

        // Apply cursor
        if ($cursor) {
            $q->where('id', '>', intval($cursor));
        }

        // Fetch extra item for next cursor
        $items = $q->limit($perPage + 1)->get();

        $nextCursor = null;
        if ($items->count() > $perPage) {
            $nextCursor = $items[$perPage]->id;
            $items = $items->slice(0, $perPage);
        }

        // Sparse fields
        if ($fields) {
            $allowed = explode(',', $fields);
            $items = $items->map(fn($u) => $u->only($allowed));
        }

        // Includes
        if ($include) {
            $rel = explode(',', $include);
            $items->load($rel);
        }

        return response()->json([
            'data' => $items->values(),
            'next_cursor' => $nextCursor,
        ]);
    }
}
