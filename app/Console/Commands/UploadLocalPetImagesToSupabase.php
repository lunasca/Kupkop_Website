<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class UploadLocalPetImagesToSupabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'supabase:sync-images {--key= : The Supabase service_role or anon key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload local pet images from storage/app/public/pets to Supabase Storage bucket';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $key = $this->option('key') ?: env('SUPABASE_KEY', env('SUPABASE_SERVICE_ROLE_KEY', env('SUPABASE_ANON_KEY')));

        if (empty($key)) {
            $this->error('Supabase key is missing! Please provide it via --key=<your_supabase_service_role_key> or set SUPABASE_KEY in your .env');
            return Command::FAILURE;
        }

        $projectRef = env('SUPABASE_PROJECT_REF', 'hlaoqkozskcyskkhubtw');
        $bucket = env('SUPABASE_STORAGE_BUCKET', 'pets');

        $dirs = [
            storage_path('app/public/pets'),
            public_path('storage/pets'),
        ];

        $filesToUpload = [];
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $scanned = scandir($dir);
                foreach ($scanned as $f) {
                    if ($f !== '.' && $f !== '..' && !str_starts_with($f, '.')) {
                        $fullPath = $dir . DIRECTORY_SEPARATOR . $f;
                        // Skip empty 70-byte placeholder files
                        if (is_file($fullPath) && filesize($fullPath) > 500) {
                            $filesToUpload[$f] = $fullPath;
                        }
                    }
                }
            }
        }

        if (empty($filesToUpload)) {
            $this->warn('No valid pet image files found to upload.');
            return Command::SUCCESS;
        }

        $this->info("Found " . count($filesToUpload) . " unique pet image files to upload to Supabase bucket '{$bucket}'...");

        $successCount = 0;
        foreach ($filesToUpload as $filename => $path) {
            $mime = mime_content_type($path) ?: 'image/jpeg';
            $contents = file_get_contents($path);

            $this->line("Uploading {$filename} (" . round(filesize($path) / 1024, 1) . " KB)...");

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $key,
                    'apikey'        => $key,
                    'Content-Type'  => $mime,
                    'x-upsert'      => 'true',
                ])->withBody($contents, $mime)
                  ->post("https://{$projectRef}.supabase.co/storage/v1/object/{$bucket}/{$filename}");

                if ($response->successful()) {
                    $this->info("  -> SUCCESS: {$filename}");
                    $successCount++;
                } else {
                    $this->error("  -> FAILED: {$filename} (Status: {$response->status()})");
                    $this->error("     Response: " . $response->body());
                }
            } catch (\Throwable $e) {
                $this->error("  -> EXCEPTION: {$filename} - " . $e->getMessage());
            }
        }

        $this->info("Done! Successfully uploaded {$successCount} of " . count($filesToUpload) . " images.");
        return Command::SUCCESS;
    }
}
