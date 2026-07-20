<?php

namespace App\Services\Indigo;

use App\Services\Ocr\OcrEngineInterface;
use App\Services\Ocr\PdfTextExtractor;

/**
 * Orchestrates reading an uploaded IndiGo document.
 *
 * For PDFs that already carry a text layer, the text is read directly (instant,
 * free, no OCR). Everything else — images, and scanned/photographed PDFs with no
 * text layer — is sent to the configured OCR engine. The recognised text is then
 * parsed into the structured fields the form needs. The controller depends only
 * on this class.
 */
class IndigoDocumentService
{
    public function __construct(
        private OcrEngineInterface $ocrEngine,
        private IndigoDocumentParser $parser,
        private PdfTextExtractor $pdfTextExtractor,
    ) {
    }

    /**
     * Extract the structured baggage fields from a document on disk.
     *
     * @return array<string, mixed>  The nine parsed fields (values may be null).
     */
    public function extractFromFile(string $absolutePath, string $mimeType): array
    {
        // 1) Fast path: a PDF with an embedded text layer needs no OCR at all.
        if ($this->isPdf($absolutePath, $mimeType)) {
            $embeddedText = $this->pdfTextExtractor->extract($absolutePath);

            if (trim($embeddedText) !== '') {
                $parsed = $this->parser->parse($embeddedText);

                if ($this->hasAnyValue($parsed)) {
                    return $parsed;
                }
            }
        }

        // 2) Fallback: OCR the document (images, scans, text-less PDFs).
        $text = $this->ocrEngine->extractText($absolutePath, $mimeType);

        return $this->parser->parse($text);
    }

    private function isPdf(string $absolutePath, string $mimeType): bool
    {
        if (str_contains(strtolower($mimeType), 'pdf')) {
            return true;
        }

        $handle = @fopen($absolutePath, 'rb');
        if ($handle === false) {
            return false;
        }

        $head = fread($handle, 4);
        fclose($handle);

        return $head === '%PDF';
    }

    private function hasAnyValue(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }
}
