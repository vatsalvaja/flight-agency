<?php

namespace App\Services\Ocr;

/**
 * Contract for any document OCR / text-extraction engine.
 *
 * Keeping this behind an interface lets us swap Google Document AI for another
 * provider (Vision API, Textract, a local engine, or a test fake) without
 * touching the parser, the service, or the controller.
 */
interface OcrEngineInterface
{
    /**
     * Read the given file and return its full extracted text.
     *
     * @param  string  $absolutePath  Absolute path to a readable file on disk.
     * @param  string  $mimeType      The file mime type (e.g. application/pdf, image/jpeg).
     * @return string                 The extracted plain text ('' when nothing could be read).
     *
     * @throws \Throwable when the engine is misconfigured or the request fails.
     */
    public function extractText(string $absolutePath, string $mimeType): string;
}
