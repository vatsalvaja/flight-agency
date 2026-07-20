<?php

namespace App\Services\Indigo;

use Carbon\Carbon;
use DateTime;

/**
 * Cleans and normalises raw values pulled out of an OCR'd IndiGo document so the
 * form receives predictable, safe values. Every method returns null when it
 * cannot produce a sensible value — a missing field never throws.
 */
class IndigoValueNormalizer
{
    /**
     * Trim and collapse internal whitespace; null when empty.
     */
    public function text(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value === '' ? null : $value;
    }

    /**
     * Normalised uppercase text (for codes such as reference numbers and PNRs).
     */
    public function upper(?string $value): ?string
    {
        $value = $this->text($value);

        return $value === null ? null : strtoupper($value);
    }

    /**
     * First integer found in the value (e.g. "2 bags" => 2).
     */
    public function bags(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        return preg_match('/\d+/', $value, $m) ? (int) $m[0] : null;
    }

    /**
     * Digits-only phone number, keeping a leading "+" if present.
     */
    public function phone(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $hasPlus = str_starts_with(trim($value), '+');
        $digits = preg_replace('/\D+/', '', $value) ?? '';

        if (strlen($digits) < 6) {
            return null;
        }

        return ($hasPlus ? '+' : '') . $digits;
    }

    /**
     * Digits-only postal / pin code (4–10 digits).
     */
    public function pincode(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (preg_match('/\d[\d\s]{2,9}\d/', $value, $m)) {
            $digits = preg_replace('/\D+/', '', $m[0]) ?? '';
            if (strlen($digits) >= 4 && strlen($digits) <= 10) {
                return $digits;
            }
        }

        return null;
    }

    /**
     * Combine given + family name into one trimmed string.
     */
    public function name(?string $given, ?string $family): ?string
    {
        $parts = array_filter([$this->text($given), $this->text($family)]);

        return empty($parts) ? null : implode(' ', $parts);
    }

    /**
     * Parse a wide range of date formats to Y-m-d. Prefers day-first (dd/mm/yyyy)
     * as used on WorldTracer / Indian documents, then falls back to Carbon.
     */
    public function date(?string $value): ?string
    {
        $value = $this->text($value);
        if ($value === null) {
            return null;
        }

        // Strip time / timezone noise, leaving the date portion.
        $candidate = preg_replace('/[T]\d{1,2}:\d{2}(:\d{2})?/i', ' ', $value) ?? $value;
        $candidate = preg_replace('/\b\d{1,2}:\d{2}(:\d{2})?\s*(am|pm)?/i', ' ', $candidate) ?? $candidate;
        $candidate = preg_replace('/\b(utc|gmt|ist|hrs?|hours?)\b/i', ' ', $candidate) ?? $candidate;
        $candidate = trim(preg_replace('/\s+/', ' ', $candidate) ?? '', " \t,;-");

        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y', 'd/m/y', 'd-m-y',
            'd M Y', 'd F Y', 'j M Y', 'j F Y', 'd M, Y', 'd F, Y',
            // WorldTracer / airline contiguous format, e.g. 12JUL26 or 12JUL2026.
            'dMy', 'dMY', 'jMy', 'jMY', 'd-M-y', 'd-M-Y',
            'Y-m-d', 'Y/m/d',
            'M d Y', 'F d Y', 'M d, Y', 'F d, Y',
            'm/d/Y',
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat('!' . $format, $candidate);
            $errors = DateTime::getLastErrors();
            $clean = $errors === false || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);

            if ($date !== false && $clean) {
                // A two-digit year ("26") parses as year 0026 — remap to this century.
                $year = (int) $date->format('Y');
                if ($year < 100) {
                    $date->setDate(
                        $year <= 68 ? 2000 + $year : 1900 + $year,
                        (int) $date->format('n'),
                        (int) $date->format('j')
                    );
                }

                return $date->format('Y-m-d');
            }
        }

        try {
            return Carbon::parse($candidate)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
