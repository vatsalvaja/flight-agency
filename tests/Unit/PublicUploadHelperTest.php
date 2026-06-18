<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicUploadHelperTest extends TestCase
{
    public function test_store_public_upload_creates_missing_directories_before_store(): void
    {
        Storage::fake('public');

        $controller = new class extends Controller {
            public function upload(UploadedFile $file, string $directory): string
            {
                return $this->storePublicUpload($file, $directory);
            }
        };

        $path = $controller->upload(
            UploadedFile::fake()->image('sample.jpg'),
            'nested/uploads'
        );

        $this->assertSame('nested/uploads/' . basename($path), $path);
        Storage::disk('public')->assertExists($path);
        $this->assertTrue(Storage::disk('public')->exists('nested/uploads'));
    }
}
