<?php

namespace App\Jobs;

use ZipArchive;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Bus\Dispatchable; // ✅ IMPORTANT
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProductImagesZip implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels; // ✅ THIS LINE FIXES IT

    protected $zipPath;
    protected $tenantId;

    public function __construct($zipPath, $tenantId)
    {
        $this->zipPath = $zipPath;
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        $imagesFolder = 'tenants/' . $this->tenantId . '/products/images';

        $zip = new ZipArchive;

        if ($zip->open(storage_path('app/public/' . $this->zipPath)) === true) {

            Storage::disk('public')->makeDirectory($imagesFolder);

            for ($i = 0; $i < $zip->numFiles; $i++) {

                $filename = $zip->getNameIndex($i);

                if (substr($filename, -1) === '/') continue;

                $basename = basename($filename);

                if (str_starts_with($basename, '._') || $basename === '.DS_Store') continue;

                $fileContent = $zip->getFromIndex($i);

                Storage::disk('public')->put($imagesFolder . '/' . $basename, $fileContent);
            }

            $zip->close();

            Storage::disk('public')->delete($this->zipPath);
        }
    }
}