<?php

namespace App\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

abstract class Controller
{
    /**
     * Store a file under public/uploads after making sure the target directory exists.
     */
    protected function storePublicUpload(UploadedFile $file, string $directory): string
    {
        $directory = trim($directory, '/');
        $relativeDirectory = 'uploads' . ($directory !== '' ? '/' . $directory : '');
        $absoluteDirectory = public_path($relativeDirectory);

        // Ensure the target upload directory exists before moving the file.
        File::ensureDirectoryExists($absoluteDirectory);

        $extension = $file->getClientOriginalExtension();
        $filename = uniqid('', true) . ($extension !== '' ? '.' . $extension : '');

        $file->move($absoluteDirectory, $filename);

        return $relativeDirectory . '/' . $filename;
    }

    /**
     * Delete a file previously stored under public/uploads.
     */
    protected function deletePublicUpload(?string $path): void
    {
        if (!$path) {
            return;
        }

        $normalizedPath = ltrim($path, '/');
        $candidates = [$normalizedPath];

        if (! str_starts_with($normalizedPath, 'uploads/')) {
            $candidates[] = 'uploads/' . $normalizedPath;
            $candidates[] = 'storage/app/public/' . $normalizedPath;
        }

        foreach ($candidates as $candidate) {
            $publicPath = public_path($candidate);
            if (File::exists($publicPath)) {
                File::delete($publicPath);
            }

            $absoluteLegacyPath = base_path($candidate);
            if (File::exists($absoluteLegacyPath)) {
                File::delete($absoluteLegacyPath);
            }
        }
    }
}
