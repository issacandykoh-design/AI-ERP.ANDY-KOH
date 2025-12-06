<?php

namespace Modules\OCR\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OCRService
{
    /**
     * Tesseract executable path
     */
    protected string $tesseractPath;

    /**
     * Supported image formats
     */
    protected array $supportedFormats = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'pdf'];

    public function __construct()
    {
        // Set Tesseract path - adjust this based on your installation
        $this->tesseractPath = config('ocr.tesseract_path', 'tesseract');
    }

    /**
     * Extract text from uploaded file
     *
     * @param UploadedFile $file
     * @param string $language
     * @return array
     */
    public function extractTextFromFile(UploadedFile $file, string $language = 'eng'): array
    {
        try {
            // Validate file
            $this->validateFile($file);

            // Store file temporarily
            $tempPath = $this->storeTemporaryFile($file);

            // Extract text using Tesseract
            $text = $this->extractText($tempPath, $language);

            // Clean up temporary file
            $this->cleanupTemporaryFile($tempPath);

            return [
                'success' => true,
                'text' => $text,
                'filename' => $file->getClientOriginalName(),
                'language' => $language
            ];

        } catch (Exception $e) {
            Log::error('OCR extraction failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'filename' => $file->getClientOriginalName() ?? 'unknown'
            ];
        }
    }

    /**
     * Extract text from file path
     *
     * @param string $filePath
     * @param string $language
     * @return string
     */
    public function extractTextFromPath(string $filePath, string $language = 'eng'): string
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        return $this->extractText($filePath, $language);
    }

    /**
     * Get available languages
     *
     * @return array
     */
    public function getAvailableLanguages(): array
    {
        try {
            $command = '"' . $this->tesseractPath . '" --list-langs 2>&1';
            $output = shell_exec($command);

            if ($output === null) {
                throw new Exception('Failed to get Tesseract languages');
            }

            $lines = explode("\n", trim($output));
            $languages = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && !str_contains($line, 'List of available languages')) {
                    $languages[] = $line;
                }
            }

            return $languages;

        } catch (Exception $e) {
            Log::error('Failed to get Tesseract languages: ' . $e->getMessage());
            return ['eng']; // Default fallback
        }
    }

    /**
     * Check if Tesseract is available
     *
     * @return bool
     */
    public function isTesseractAvailable(): bool
    {
        try {
            $command = '"' . $this->tesseractPath . '" --version 2>&1';
            $output = shell_exec($command);

            return $output !== null && str_contains($output, 'tesseract');
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get Tesseract version
     *
     * @return string|null
     */
    public function getTesseractVersion(): ?string
    {
        try {
            $command = '"' . $this->tesseractPath . '" --version 2>&1';
            $output = shell_exec($command);

            if ($output && preg_match('/tesseract\s+([\d.]+)/', $output, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @throws Exception
     */
    protected function validateFile(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw new Exception('Invalid file upload');
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $this->supportedFormats)) {
            throw new Exception('Unsupported file format. Supported formats: ' . implode(', ', $this->supportedFormats));
        }

        // Check file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if ($file->getSize() > $maxSize) {
            throw new Exception('File size too large. Maximum size: 10MB');
        }
    }

    /**
     * Store file temporarily
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function storeTemporaryFile(UploadedFile $file): string
    {
        $tempDir = storage_path('app/temp/ocr');

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $filename = uniqid('ocr_') . '.' . $file->getClientOriginalExtension();
        $tempPath = $tempDir . '/' . $filename;

        $file->move($tempDir, $filename);

        return $tempPath;
    }

    /**
     * Extract text using Tesseract
     *
     * @param string $filePath
     * @param string $language
     * @return string
     */
    protected function extractText(string $filePath, string $language): string
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'ocr_output');

        // Build Tesseract command
        $command = sprintf(
            '"%s" "%s" "%s" -l %s 2>&1',
            $this->tesseractPath,
            $filePath,
            $outputFile,
            $language
        );

        // Execute Tesseract
        $output = shell_exec($command);

        // Read the output file
        $textFile = $outputFile . '.txt';

        if (!file_exists($textFile)) {
            throw new Exception('Tesseract failed to process the image. Output: ' . $output);
        }

        $text = file_get_contents($textFile);

        // Clean up temporary files
        if (file_exists($textFile)) {
            unlink($textFile);
        }
        if (file_exists($outputFile)) {
            unlink($outputFile);
        }

        return trim($text);
    }

    /**
     * Clean up temporary file
     *
     * @param string $filePath
     */
    protected function cleanupTemporaryFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Get supported file formats
     *
     * @return array
     */
    public function getSupportedFormats(): array
    {
        return $this->supportedFormats;
    }
}
