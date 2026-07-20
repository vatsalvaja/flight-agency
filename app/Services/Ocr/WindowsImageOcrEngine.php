<?php

namespace App\Services\Ocr;

use RuntimeException;
use Symfony\Component\Process\Process;

class WindowsImageOcrEngine implements OcrEngineInterface
{
    public function extractText(string $absolutePath, string $mimeType): string
    {
        if (! $this->isImage($mimeType)) {
            return '';
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            throw new RuntimeException('Windows image OCR is only available on Windows.');
        }

        if (! is_file($absolutePath)) {
            throw new RuntimeException('Image file could not be read for OCR.');
        }

        $script = __DIR__ . DIRECTORY_SEPARATOR . 'windows-image-ocr.ps1';
        if (! is_file($script)) {
            throw new RuntimeException('Windows OCR script is missing.');
        }

        $profileRoot = storage_path('app/ocr-windows-profile');
        $tempRoot = storage_path('app/ocr-temp');

        foreach ([
            $profileRoot,
            $profileRoot . DIRECTORY_SEPARATOR . 'AppData' . DIRECTORY_SEPARATOR . 'Roaming',
            $profileRoot . DIRECTORY_SEPARATOR . 'AppData' . DIRECTORY_SEPARATOR . 'Local',
            $tempRoot,
        ] as $directory) {
            if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
                throw new RuntimeException("Unable to create OCR runtime directory: {$directory}");
            }
        }

        $process = new Process([
            $this->powershellPath(),
            '-NoLogo',
            '-NoProfile',
            '-NonInteractive',
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            $script,
            '-Path',
            $absolutePath,
        ], null, [
            'USERPROFILE' => $profileRoot,
            'APPDATA' => $profileRoot . DIRECTORY_SEPARATOR . 'AppData' . DIRECTORY_SEPARATOR . 'Roaming',
            'LOCALAPPDATA' => $profileRoot . DIRECTORY_SEPARATOR . 'AppData' . DIRECTORY_SEPARATOR . 'Local',
            'TEMP' => $tempRoot,
            'TMP' => $tempRoot,
        ]);
        $process->setTimeout(30);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException($this->cleanProcessOutput($process->getErrorOutput()) ?: 'Windows image OCR failed.');
        }

        return trim($process->getOutput());
    }

    private function powershellPath(): string
    {
        $systemRoot = rtrim((string) (getenv('SystemRoot') ?: 'C:\Windows'), '\\/');
        $candidates = [
            $systemRoot . '\Sysnative\WindowsPowerShell\v1.0\powershell.exe',
            $systemRoot . '\System32\WindowsPowerShell\v1.0\powershell.exe',
            'powershell.exe',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate === 'powershell.exe' || is_file($candidate)) {
                return $candidate;
            }
        }

        return 'powershell.exe';
    }

    private function cleanProcessOutput(string $output): string
    {
        if (str_contains($output, "\0")) {
            $converted = @mb_convert_encoding($output, 'UTF-8', 'UTF-16LE');
            if (is_string($converted) && $converted !== '') {
                $output = $converted;
            }
        }

        return trim($output);
    }

    private function isImage(string $mimeType): bool
    {
        return in_array(strtolower($mimeType), ['image/jpeg', 'image/jpg', 'image/png'], true);
    }
}
