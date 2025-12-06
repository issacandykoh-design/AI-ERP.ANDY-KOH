<?php

use Illuminate\Support\Facades\Route;
use Modules\OCR\Http\Controllers\OCRController;

/*
|--------------------------------------------------------------------------
| OCR Module Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the OCR module.
|
*/

Route::prefix('ocr')->name('ocr.')->group(function () {
    
    // Main OCR page
    Route::get('/', [OCRController::class, 'index'])->name('index');
    
    // OCR processing routes
    Route::post('/process', [OCRController::class, 'processOCR'])->name('process');
    Route::post('/process-url', [OCRController::class, 'processFromUrl'])->name('process.url');
    
    // API routes for getting system information
    Route::get('/languages', [OCRController::class, 'getLanguages'])->name('languages');
    Route::get('/status', [OCRController::class, 'getStatus'])->name('status');
    
});