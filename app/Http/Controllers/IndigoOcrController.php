<?php

namespace App\Http\Controllers;

use App\Services\Indigo\IndigoDocumentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Handles the AJAX "read an IndiGo baggage document" request used by the Assign
 * Luggage create/edit forms. It only validates the upload, delegates the actual
 * OCR + parsing to {@see IndigoDocumentService}, persists the source document for
 * audit/reprocessing, and shapes the JSON response. No OCR logic lives here.
 *
 * The route sits behind the `admin.auth` middleware, so unauthenticated requests
 * never reach this controller.
 */
class IndigoOcrController extends Controller
{
    /** Generic, user-safe failure message — internal errors are never exposed. */
    private const FAILURE_MESSAGE = 'Unable to read the uploaded document. Please upload a clearer image or enter the details manually.';

    public function __construct(private IndigoDocumentService $documentService)
    {
    }

    public function extract(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'indigo_document' => 'required|file|mimes:jpeg,jpg,png,pdf|max:8192',
        ], [
            'indigo_document.required' => 'Please choose a document to upload.',
            'indigo_document.file' => 'The uploaded item is not a valid file.',
            'indigo_document.mimes' => 'Only JPG, JPEG, PNG or PDF files are allowed.',
            'indigo_document.max' => 'The document must not be larger than 8 MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('indigo_document'),
            ], 422);
        }

        $file = $request->file('indigo_document');
        $mimeType = $file->getMimeType() ?: $file->getClientMimeType();

        // Read + OCR the temp upload BEFORE it is moved to permanent storage.
        try {
            $data = $this->documentService->extractFromFile($file->getRealPath(), $mimeType);
        } catch (\Throwable $e) {
            Log::error('IndiGo OCR failure: ' . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => self::FAILURE_MESSAGE,
            ]);
        }

        $hasAnyValue = collect($data)->contains(fn ($value) => $value !== null && $value !== '');

        if (! $hasAnyValue) {
            return response()->json([
                'success' => false,
                'message' => self::FAILURE_MESSAGE,
            ]);
        }

        // Persist the source document permanently for verification / disputes /
        // reprocessing. Stored only on a successful read so we never keep orphans.
        $storedPath = $this->storePublicUpload($file, 'indigo-documents');
        $data['document_path'] = $storedPath;
        $data['document_url'] = asset($storedPath);

        return response()->json([
            'success' => true,
            'message' => 'Document details extracted successfully.',
            'data' => $data,
        ]);
    }
}
