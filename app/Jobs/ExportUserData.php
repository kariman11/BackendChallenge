<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use ZipArchive;

class ExportUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function handle()
    {
        $user = User::with(['organizations', 'roles'])->findOrFail($this->userId);

        // Create temp directory
        $dir = storage_path("app/gdpr/{$user->id}");
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        // Export user profile JSON
        file_put_contents("$dir/user.json", json_encode($user, JSON_PRETTY_PRINT));

        // Export related data
        file_put_contents("$dir/organizations.json", json_encode($user->organizations, JSON_PRETTY_PRINT));
        file_put_contents("$dir/roles.json", json_encode($user->roles, JSON_PRETTY_PRINT));

        // Create ZIP
        $zipPath = "gdpr_exports/user_{$user->id}.zip";
        $zipFullPath = storage_path("app/$zipPath");

        $zip = new ZipArchive;
        $zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (glob("$dir/*.json") as $file) {
            $zip->addFile($file, basename($file));
        }

        $zip->close();

        // Save record on user
        $user->gdpr_export_zip = $zipPath;
        $user->gdpr_export_ready_at = now();
        $user->save();

        // Email the user
        Mail::raw(
            "Your GDPR export is ready. Download link: " . url("/api/users/{$user->id}/export/download"),
            fn($m) => $m->to($user->email)->subject('Your GDPR Data Export')
        );
    }
}
