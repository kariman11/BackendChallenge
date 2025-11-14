<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RollupLoginDaily extends Command
{
    protected $signature = 'analytics:rollup-logins {--date=}';

    protected $description = 'Roll up login_events into login_daily';

    public function handle()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $start = $date->copy()->startOfDay();
        $end   = $date->copy()->endOfDay();

        $rows = DB::table('login_events')
            ->select('user_id', 'organization_id', DB::raw('DATE(logged_in_at) as date'), DB::raw('count(*) as cnt'))
            ->whereBetween('logged_in_at', [$start, $end])
            ->groupBy('user_id','organization_id','date')
            ->get();

        foreach ($rows as $r) {
            DB::table('login_daily')->updateOrInsert(
                [
                    'user_id' => $r->user_id,
                    'organization_id' => $r->organization_id,
                    'date' => $r->date,
                ],
                [
                    'count' => $r->cnt,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $this->info("Rolled up logins for {$date->toDateString()}");
    }

}
