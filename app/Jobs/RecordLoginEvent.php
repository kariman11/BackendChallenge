<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;    // ✔ REQUIRED
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecordLoginEvent
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;
    public $orgId;
    public $ip;
    public $agent;

    public function __construct($userId, $orgId, $ip, $agent)
    {
        $this->userId = $userId;
        $this->orgId  = $orgId;
        $this->ip     = $ip;
        $this->agent  = $agent;
    }

    public function handle()
    {
        \DB::table('login_events')->insert([
            'user_id'        => $this->userId,
            'organization_id'=> $this->orgId,
            'ip_address'     => $this->ip,
            'user_agent'     => $this->agent,
            'logged_in_at'   => now(),
        ]);
    }
}
