<?php

namespace App\Jobs;

use ZipArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\Laravel\Facades\Image;

class ProcessProductImagesZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $zipPath;
    protected $tenantId;

    public function __construct($zipPath, $tenantId)
    {
        $this->zipPath = $zipPath;
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        $basePath = "tenants/{$this->tenantId}/products/images";
        $thumbPath = "{$basePath}/thumbs";

        $zipFilePath = storage_path('app/public/' . $this->zipPath);

        $zip = new ZipArchive;

        if ($zip->open($zipFilePath) !== true) {
            return;
        }

        // create folders
        Storage::disk('public')->makeDirectory($basePath);
        Storage::disk('public')->makeDirectory($thumbPath);

        for ($i = 0; $i < $zip->numFiles; $i++) {

            $filename = $zip->getNameIndex($i);

            // skip folders
            if (substr($filename, -1) === '/') continue;

            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $basename = strtolower(pathinfo($filename, PATHINFO_FILENAME));

            // skip junk files
            if (
                str_starts_with($basename, '._') ||
                $basename === '.ds_store'
            ) continue;

            $fileContent = $zip->getFromIndex($i);
            if (!$fileContent) continue;

            try {

                // =========================
                // 1️⃣ STORE ORIGINAL IMAGE
                // =========================
                $originalName = "{$basename}.{$extension}";
                $originalFullPath = "{$basePath}/{$originalName}";

                Storage::disk('public')->put($originalFullPath, $fileContent);

                // =========================
                // 2️⃣ MAIN OPTIMIZED IMAGE
                // =========================
                $mainImage = Image::read($fileContent)
                    ->scale(width: 800)
                    ->toWebp(75);

                $webpPath = "{$basePath}/{$basename}.webp";

                Storage::disk('public')->put($webpPath, $mainImage);

                // =========================
                // 3️⃣ THUMBNAIL (POS GRID)
                // =========================
                $thumbImage = Image::read($fileContent)
                    ->scale(width: 300)
                    ->toWebp(70);

                $thumbFullPath = "{$thumbPath}/{$basename}.webp";

                Storage::disk('public')->put($thumbFullPath, $thumbImage);

            } catch (\Exception $e) {
                logger()->error("Image failed: " . $e->getMessage());
                continue;
            }
        }

        $zip->close();

        // delete zip after processing
        Storage::disk('public')->delete($this->zipPath);
    }
}