<?php

return [
    'name' => 'OCR',
    
    /*
    |--------------------------------------------------------------------------
    | Tesseract Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the path to Tesseract executable and other OCR settings
    |
    */
    
    'tesseract_path' => env('TESSERACT_PATH', 'tesseract'),
    
    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | Default language for OCR processing
    | Common options: eng, chi_sim, chi_tra, jpn, kor, etc.
    |
    */
    
    'default_language' => env('OCR_DEFAULT_LANGUAGE', 'eng'),
    
    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configure file upload limits and allowed formats
    |
    */
    
    'max_file_size' => env('OCR_MAX_FILE_SIZE', 10485760), // 10MB in bytes
    
    'allowed_formats' => [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'pdf'
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure temporary file storage settings
    |
    */
    
    'temp_storage_path' => storage_path('app/temp/ocr'),
    
    'cleanup_temp_files' => env('OCR_CLEANUP_TEMP_FILES', true),
    
    /*
    |--------------------------------------------------------------------------
    | Processing Options
    |--------------------------------------------------------------------------
    |
    | Additional Tesseract processing options
    |
    */
    
    'tesseract_options' => [
        // Page segmentation mode (PSM)
        // 3 = Fully automatic page segmentation, but no OSD (default)
        // 6 = Uniform block of text
        // 8 = Single word
        // 13 = Raw line. Treat the image as a single text line
        'psm' => env('OCR_PSM', 3),
        
        // OCR Engine Mode (OEM)
        // 0 = Legacy engine only
        // 1 = Neural nets LSTM engine only
        // 2 = Legacy + LSTM engines
        // 3 = Default, based on what is available
        'oem' => env('OCR_OEM', 3),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable OCR operation logging
    |
    */
    
    'enable_logging' => env('OCR_ENABLE_LOGGING', true),
];