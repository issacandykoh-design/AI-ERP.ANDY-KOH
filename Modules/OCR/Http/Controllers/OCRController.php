<?php

namespace Modules\OCR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\OCR\Services\OCRService;
use Modules\OCR\Http\Requests\OCRRequest;

class OCRController extends Controller
{
    protected OCRService $ocrService;

    public function __construct(OCRService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Display OCR upload form
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $availableLanguages = $this->ocrService->getAvailableLanguages();
        $supportedFormats = $this->ocrService->getSupportedFormats();
        $tesseractVersion = $this->ocrService->getTesseractVersion();
        $isTesseractAvailable = $this->ocrService->isTesseractAvailable();

        return view('ocr::index', compact(
            'availableLanguages',
            'supportedFormats',
            'tesseractVersion',
            'isTesseractAvailable'
        ));
    }

    /**
     * Process OCR request
     *
     * @param OCRRequest $request
     * @return JsonResponse
     */
    public function processOCR(OCRRequest $request): JsonResponse
    {
        try {
            $file = $request->file('image');
            $language = $request->input('language', config('ocr.default_language', 'eng'));

            // Check if Tesseract is available
            if (!$this->ocrService->isTesseractAvailable()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Tesseract OCR is not available on this system'
                ], 500);
            }

            // Process the image
            $result = $this->ocrService->extractTextFromFile($file, $language);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'text' => $result['text'],
                        'filename' => $result['filename'],
                        'language' => $result['language'],
                        'word_count' => str_word_count($result['text']),
                        'character_count' => strlen($result['text'])
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An error occurred while processing the image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available languages
     *
     * @return JsonResponse
     */
    public function getLanguages(): JsonResponse
    {
        try {
            $languages = $this->ocrService->getAvailableLanguages();
            
            return response()->json([
                'success' => true,
                'languages' => $languages
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get available languages'
            ], 500);
        }
    }

    /**
     * Get system status
     *
     * @return JsonResponse
     */
    public function getStatus(): JsonResponse
    {
        try {
            $status = [
                'tesseract_available' => $this->ocrService->isTesseractAvailable(),
                'tesseract_version' => $this->ocrService->getTesseractVersion(),
                'supported_formats' => $this->ocrService->getSupportedFormats(),
                'available_languages' => $this->ocrService->getAvailableLanguages(),
                'max_file_size' => config('ocr.max_file_size'),
                'default_language' => config('ocr.default_language')
            ];

            return response()->json([
                'success' => true,
                'status' => $status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get system status'
            ], 500);
        }
    }

    /**
     * Process OCR from URL (for API usage)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processFromUrl(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'language' => 'string|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');
            $language = $request->input('language', config('ocr.default_language', 'eng'));

            // Download image from URL
            $imageContent = file_get_contents($url);
            if ($imageContent === false) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to download image from URL'
                ], 400);
            }

            // Save to temporary file
            $tempPath = tempnam(sys_get_temp_dir(), 'ocr_url_');
            file_put_contents($tempPath, $imageContent);

            // Process with OCR
            $text = $this->ocrService->extractTextFromPath($tempPath, $language);

            // Clean up
            unlink($tempPath);

            return response()->json([
                'success' => true,
                'data' => [
                    'text' => $text,
                    'url' => $url,
                    'language' => $language,
                    'word_count' => str_word_count($text),
                    'character_count' => strlen($text)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process image from URL: ' . $e->getMessage()
            ], 500);
        }
    }
}