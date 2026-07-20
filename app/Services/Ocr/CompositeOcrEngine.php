<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Log;

class CompositeOcrEngine implements OcrEngineInterface
{
    public function __construct(
        private TesseractOcrEngine $tesseractOcr,
        private WindowsImageOcrEngine $windowsImageOcr,
        private GoogleDocumentAiOcrService $googleDocumentAi,
    ) {
    }

    public function extractText(string $absolutePath, string $mimeType): string
    {
        if ($this->isImage($mimeType)) {
            try {
                $text = $this->tesseractOcr->extractText($absolutePath, $mimeType);

                if (trim($text) !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                Log::warning('Tesseract image OCR failed, falling back to Windows OCR.', [
                    'message' => $e->getMessage(),
                ]);
            }

            try {
                $text = $this->windowsImageOcr->extractText($absolutePath, $mimeType);

                if (trim($text) !== '') {
                    return $text;
                }
            } catch (\Throwable $e) {
                Log::warning('Windows image OCR failed, falling back to Google Document AI.', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $this->googleDocumentAi->extractText($absolutePath, $mimeType);
    }

    private function isImage(string $mimeType): bool
    {
        return in_array(strtolower($mimeType), ['image/jpeg', 'image/jpg', 'image/png'], true);
    }
}
