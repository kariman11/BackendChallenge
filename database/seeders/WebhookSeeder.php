<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebhookSeeder extends Seeder
{
    public function run()
    {
        // Make sure an org exists
        $orgId = DB::table('organizations')->first()->id ?? null;

        if (! $orgId) {
            // Create one for testing
            $orgId = DB::table('organizations')->insertGetId([
                'name' => 'Test Organization',
                'owner_id' => 1, // you can adjust based on your users table
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Your testing endpoint (replace with your actual webhook.site URL)
        $testUrl = 'https://webhook.site/your-unique-id-here';

        // Prevent duplicate entries
        $existing = DB::table('webhooks')->where('organization_id', $orgId)->first();

        if ($existing) {
            echo "Webhook already exists for org {$orgId}\n";
            return;
        }

        // Insert webhook entry
        DB::table('webhooks')->insert([
            'organization_id' => $orgId,
            'url'             => $testUrl,
            'secret'          => Str::random(40), // HMAC secret
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        echo "Webhook created successfully for org {$orgId}\n";
    }
}
