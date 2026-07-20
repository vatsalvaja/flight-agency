<?php

namespace App\Services\Ocr;

/**
 * Extracts the embedded text layer from a PDF using pure PHP (no external
 * dependency, no OCR).
 *
 * Text-based PDFs (WorldTracer desktop exports, the bundled demo receipt, most
 * "print to PDF" files) carry a real text layer that can be read directly —
 * instantly and for free. Scanned or photographed PDFs have no text layer, so
 * this returns '' and the caller falls back to the OCR engine.
 */
class PdfTextExtractor
{
    /**
     * @return string  The recognised text, or '' when there is no text layer.
     */
    public function extract(string $absolutePath): string
    {
        $data = @file_get_contents($absolutePath);
        if ($data === false || strncmp($data, '%PDF', 4) !== 0) {
            return '';
        }

        $text = '';

        if (preg_match_all('/stream\r?\n(.*?)\r?\nendstream/s', $data, $matches)) {
            foreach ($matches[1] as $stream) {
                $content = $this->maybeInflate($stream);
                $shown = $this->extractShownText($content);
                if ($shown !== '') {
                    $text .= $shown . "\n";
                }
            }
        }

        return trim($text);
    }

    /**
     * Inflate FlateDecode (zlib) content streams when possible; otherwise return
     * the stream unchanged (e.g. an uncompressed stream).
     */
    private function maybeInflate(string $stream): string
    {
        $inflated = @gzuncompress($stream);
        if ($inflated === false) {
            $inflated = @gzinflate($stream);
        }

        return ($inflated !== false && $inflated !== '') ? $inflated : $stream;
    }

    /**
     * Pull the visible strings from a content stream in document order, treating
     * each Tj / TJ show operator as one line.
     */
    private function extractShownText(string $content): string
    {
        $pattern = '/(\((?:\\\\.|[^\\\\()])*\)\s*Tj)|(\[(?:[^\[\]\\\\]|\\\\.)*\]\s*TJ)/s';

        if (! preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            return '';
        }

        $lines = [];

        foreach ($matches as $match) {
            $token = $match[0];

            if (substr(rtrim($token), -2) === 'Tj') {
                $lines[] = $this->decodeLiteral($this->firstLiteral($token));
                continue;
            }

            // [ (a) (b) ] TJ — several fragments shown on the same line.
            if (preg_match_all('/\((?:\\\\.|[^\\\\()])*\)/s', $token, $parts)) {
                $line = '';
                foreach ($parts[0] as $part) {
                    $line .= $this->decodeLiteral($part);
                }
                $lines[] = $line;
            }
        }

        return implode("\n", $lines);
    }

    private function firstLiteral(string $token): string
    {
        return preg_match('/\((?:\\\\.|[^\\\\()])*\)/s', $token, $m) ? $m[0] : '()';
    }

    private function decodeLiteral(string $literal): string
    {
        $inner = substr($literal, 1, -1);

        return strtr($inner, [
            '\\(' => '(',
            '\\)' => ')',
            '\\\\' => '\\',
            '\\n' => "\n",
            '\\r' => "\r",
            '\\t' => "\t",
        ]);
    }
}
