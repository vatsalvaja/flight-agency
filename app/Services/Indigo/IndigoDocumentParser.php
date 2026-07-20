<?php

namespace App\Services\Indigo;

/**
 * Turns the raw OCR text of an IndiGo delayed-baggage document into the nine
 * structured fields the Assign Luggage form expects.
 *
 * The strategy is label-anchored: for each field we look for one of several
 * known labels at the start of a line and capture the value after it (or on the
 * following line). Short code-like fields get a regex fallback. Any field that
 * cannot be found is returned as null so partial documents still work.
 */
class IndigoDocumentParser
{
    public function __construct(private IndigoValueNormalizer $normalizer)
    {
    }

    /**
     * @return array{
     *     reference_number: ?string, number_of_bags: ?int, pickup_date: ?string,
     *     delivery_date: ?string, pnr_number: ?string, customer_name: ?string,
     *     contact_number: ?string, address: ?string, pincode: ?string
     * }
     */
    public function parse(string $text): array
    {
        $lines = $this->toLines($text);

        $reference = $this->findValue($lines, [
            'File Reference Number', 'File Reference', 'Reference Number', 'File Ref', 'AHL Reference', 'Reference No', 'Reference',
        ]);
        $reference = $this->firstMatch($reference, '/[A-Z0-9][A-Z0-9\-\/]{4,}/i')
            ?? $this->firstMatch($text, '/\b[A-Z]{3}[A-Z0-9]{2}\d{4,6}\b/');

        $bags = $this->findValue($lines, [
            'Number of Bags', 'No. of Bags', 'No of Bags', 'Number Of Bags', 'Total Bags', 'Bags', 'Pieces',
        ]);

        $creationDate = $this->findValue($lines, [
            'Creation Date', 'Date Created', 'Created On', 'File Creation Date', 'Report Date', 'Created',
        ]);

        $deliveryDate = $this->findValue($lines, [
            'Delivery Date', 'Expected Delivery Date', 'Expected Delivery', 'Date of Delivery',
        ]);

        $pnr = $this->findValue($lines, [
            'PNR Number', 'PNR No', 'PNR', 'Record Locator', 'Booking Reference', 'Booking Ref',
        ]);
        $pnr = $this->firstMatch($pnr, '/[A-Z0-9]{5,8}/i');
        if ($pnr === null && preg_match('/\bPNR\b[^A-Z0-9]{0,8}([A-Z0-9]{6})\b/i', $text, $m)) {
            $pnr = $m[1];
        }

        $given = $this->findValue($lines, ['Given Name', 'Given Names', 'First Name']);
        $family = $this->findValue($lines, ['Family Name', 'Last Name', 'Surname']);
        $customerName = $this->normalizer->name($given, $family);
        if ($customerName === null) {
            $customerName = $this->normalizer->text(
                $this->findValue($lines, ['Passenger Name', 'Customer Name', 'Name'])
            );
        }

        $contact = $this->findValue($lines, ['Cell Phone', 'Cell Number', 'Cell', 'Mobile Phone', 'Mobile Number', 'Mobile'])
            ?? $this->findValue($lines, ['Home Phone', 'Residence Phone', 'Home'])
            ?? $this->findValue($lines, ['Business Phone', 'Work Phone', 'Office Phone', 'Business'])
            ?? $this->findValue($lines, ['Telephone', 'Contact Number', 'Phone', 'Contact']);

        $address = $this->findValue($lines, [
            'Address Line', 'Street Address', 'Delivery Address', 'Permanent Address', 'Address',
        ]);

        $pincode = $this->findValue($lines, [
            'Postal Code', 'Pin Code', 'Pincode', 'PIN', 'Zip Code', 'Zip',
        ]);

        return [
            'reference_number' => $this->normalizer->upper($reference),
            'number_of_bags' => $this->normalizer->bags($bags),
            'pickup_date' => $this->normalizer->date($creationDate),
            'delivery_date' => $this->normalizer->date($deliveryDate),
            'pnr_number' => $this->normalizer->upper($pnr),
            'customer_name' => $customerName,
            'contact_number' => $this->normalizer->phone($contact),
            'address' => $this->normalizer->text($address),
            'pincode' => $this->normalizer->pincode($pincode),
        ];
    }

    /**
     * Split into trimmed, non-empty lines.
     *
     * @return array<int, string>
     */
    private function toLines(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_map(static fn ($line) => trim($line), explode("\n", $text));

        return array_values(array_filter($lines, static fn ($line) => $line !== ''));
    }

    /**
     * Return the value that follows the first matching label (same line, or the
     * next line when the label sits alone).
     *
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $labels
     */
    private function findValue(array $lines, array $labels): ?string
    {
        foreach ($lines as $index => $line) {
            foreach ($labels as $label) {
                $quoted = preg_quote($label, '/');

                if (preg_match('/^' . $quoted . '\s*[:\-]?\s*(.+)$/i', $line, $m)) {
                    $value = trim($m[1], " \t:-");
                    if ($value !== '') {
                        return $value;
                    }
                }

                if (preg_match('/^' . $quoted . '\s*[:\-]?\s*$/i', $line) && isset($lines[$index + 1])) {
                    return $lines[$index + 1];
                }
            }
        }

        return null;
    }

    /**
     * First substring of $value matching $pattern, or null.
     */
    private function firstMatch(?string $value, string $pattern): ?string
    {
        if ($value === null) {
            return null;
        }

        return preg_match($pattern, $value, $m) ? $m[0] : null;
    }
}
