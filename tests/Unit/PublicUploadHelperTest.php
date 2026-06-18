<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PublicUploadHelperTest extends TestCase
{
    public function test_store_public_upload_creates_missing_directories_before_store(): void
    {
        $controller = new class extends Controller {
            public function upload(UploadedFile $file, string $directory): string
            {
                return $this->storePublicUpload($file, $directory);
            }
        };

        $path = null;

        try {
            $path = $controller->upload(
                UploadedFile::fake()->image('sample.jpg'),
                'nested/uploads'
            );

            $fullPath = public_path($path);

            $this->assertSame('uploads/nested/uploads/' . basename($path), $path);
            $this->assertTrue(File::exists($fullPath));
            $this->assertTrue(File::isDirectory(public_path('uploads/nested/uploads')));
        } finally {
            if ($path) {
                File::delete(public_path($path));
            }
        }
    }
}
