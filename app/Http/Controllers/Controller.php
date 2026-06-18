<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    /**
     * Store a file on the public disk after making sure the target directory exists.
     */
    protected function storePublicUpload(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');

        // Ensure the storage root and nested upload directory exist on first upload.
        File::ensureDirectoryExists(storage_path('app/public'));
        Storage::disk('public')->makeDirectory($directory);

        return $file->store($directory, 'public');
    }
}
