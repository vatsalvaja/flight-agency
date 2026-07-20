<?php

namespace App\Services\Ocr;

use RuntimeException;
use Symfony\Component\Process\Process;

class TesseractOcrEngine implements OcrEngineInterface
{
    public function extractText(string $absolutePath, string $mimeType): string
    {
        if (! $this->isImage($mimeType)) {
            return '';
        }

        if (! is_file($absolutePath)) {
            throw new RuntimeException('Image file could not be read for OCR.');
        }

        $binary = $this->binaryPath();

        if ($binary === null) {
            throw new RuntimeException('Tesseract OCR is not installed.');
        }

        $process = new Process([$binary, $absolutePath, 'stdout', '-l', 'eng', '--psm', '6']);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'Tesseract OCR failed.');
        }

        return trim($process->getOutput());
    }

    private function binaryPath(): ?string
    {
        $candidates = array_filter([
            env('TESSERACT_PATH'),
            'C:\Program Files\Tesseract-OCR\tesseract.exe',
            'C:\Program Files (x86)\Tesseract-OCR\tesseract.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $process = new Process(PHP_OS_FAMILY === 'Windows' ? ['where.exe', 'tesseract'] : ['which', 'tesseract']);
        $process->setTimeout(5);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $path = trim(strtok($process->getOutput(), "\r\n") ?: '');

        return $path !== '' ? $path : null;
    }

    private function isImage(string $mimeType): bool
    {
        return in_array(strtolower($mimeType), ['image/jpeg', 'image/jpg', 'image/png'], true);
    }
}
