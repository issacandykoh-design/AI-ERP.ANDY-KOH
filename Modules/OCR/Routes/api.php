<?php

use Illuminate\Support\Facades\Route;
use Modules\OCR\Http\Controllers\OCRController;

/*
|--------------------------------------------------------------------------
| OCR API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the OCR module.
|
*/

Route::prefix('ocr')->name('api.ocr.')->group(function () {
    
    // OCR processing API endpoints
    Route::post('/extract', [OCRController::class, 'processOCR'])->name('extract');
    Route::post('/extract-from-url', [OCRController::class, 'processFromUrl'])->name('extract.url');
    
    // System information API endpoints
    Route::get('/languages', [OCRController::class, 'getLanguages'])->name('languages');
    Route::get('/status', [OCRController::class, 'getStatus'])->name('status');
    
});