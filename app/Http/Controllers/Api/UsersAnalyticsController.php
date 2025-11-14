<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginDaily;
use App\Models\LoginEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UsersAnalyticsController extends Controller
{
    // GET /api/users/top-logins?window=7d&org_id=1&limit=10
    public function topLogins(Request $request)
    {
        $window = $request->query('window', '7d'); // '7d' or '30d'
        $orgId = $request->query('org_id');
        $limit = intval($request->query('limit', 10));

        $days = ($window === '30d') ? 30 : 7;
        $from = Carbon::today()->subDays($days)->toDateString();

        $q = DB::table('login_daily')
            ->select('user_id', DB::raw('SUM(count) as total'))
            ->where('date', '>=', $from)
            ->groupBy('user_id')
            ->orderByDesc('total');

        if ($orgId) $q->where('organization_id', $orgId);
        $rows = $q->limit($limit)->get();

        $userIds = $rows->pluck('user_id')->all();
        $users = User::whereIn('id', $userIds)->get()->keyBy('id');

        $data = $rows->map(function($r) use ($users) {
            $u = $users->get($r->user_id);
            return [
                'user_id' => $r->user_id,
                'name' => $u ? $u->name : null,
                'email' => $u ? $u->email : null,
                'count' => intval($r->total),
            ];
        });

        return response()->json(['data' => $data]);
    }

    // GET /api/users/inactive?window=week&org_id=1&cursor=...
    public function inactive(Request $request)
    {
        $window = $request->query('window','week'); // hour|day|week|month
        $orgId = $request->query('org_id');
        $perPage = min(100, intval($request->query('per_page', 20)));
        $cursor = $request->query('cursor');

        $threshold = match($window) {
            'hour' => Carbon::now()->subHour(),
            'day'  => Carbon::now()->subDay(),
            'week' => Carbon::now()->subWeek(),
            'month'=> Carbon::now()->subMonth(),
            default => Carbon::now()->subWeek(),
        };

        // build base query filtered by org if provided
        $base = User::query()->where(function($q) use ($threshold) {
            $q->whereNull('last_login_at')->orWhere('last_login_at', '<', $threshold);
        });

        if ($orgId) {
            // users who belong to the org (via pivot)
            $base->whereHas('organizations', function($q) use ($orgId) {
                $q->where('organizations.id', $orgId);
            });
        }

        // cursor pagination using last_login_at + id stable sort
        $base->orderBy('last_login_at', 'asc')->orderBy('id','asc');

        if ($cursor) {
            // cursor format: base64(last_login_at|id) e.g. 2025-11-01 10:00:00|123
            [$lastLoginAt, $lastId] = explode('|', base64_decode($cursor));
            $base->where(function($q) use ($lastLoginAt, $lastId) {
                $q->where('last_login_at', '>', $lastLoginAt)
                    ->orWhere(function($q2) use ($lastLoginAt, $lastId) {
                        $q2->where('last_login_at', $lastLoginAt)
                            ->where('id', '>', $lastId);
                    });
            });
        }

        $items = $base->limit($perPage + 1)->get();

        $next = null;
        if ($items->count() > $perPage) {
            $last = $items->slice($perPage, 1)->first();
            $next = base64_encode(($last->last_login_at ? $last->last_login_at->toDateTimeString() : '') . '|' . $last->id);
            $items = $items->slice(0, $perPage);
        }

        return response()->json([
            'data' => $items->values(),
            'next_cursor' => $next,
        ]);
    }
}
