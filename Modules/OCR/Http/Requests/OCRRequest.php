<?php

namespace Modules\OCR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OCRRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $maxFileSize = config('ocr.max_file_size', 10485760); // 10MB default
        $allowedFormats = implode(',', config('ocr.allowed_formats', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'pdf']));

        return [
            'image' => [
                'required',
                'file',
                'max:' . ($maxFileSize / 1024), // Convert bytes to KB for Laravel validation
                'mimes:' . $allowedFormats
            ],
            'language' => [
                'sometimes',
                'string',
                'max:10'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        $maxSizeMB = config('ocr.max_file_size', 10485760) / 1024 / 1024;
        $allowedFormats = config('ocr.allowed_formats', ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'pdf']);

        return [
            'image.required' => 'Please select an image file to process.',
            'image.file' => 'The uploaded file is not valid.',
            'image.max' => "The image file size must not exceed {$maxSizeMB}MB.",
            'image.mimes' => 'The image must be a file of type: ' . implode(', ', $allowedFormats) . '.',
            'language.string' => 'The language must be a valid string.',
            'language.max' => 'The language code must not exceed 10 characters.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'image' => 'image file',
            'language' => 'language code'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional custom validation can be added here
            if ($this->hasFile('image')) {
                $file = $this->file('image');
                
                // Check if file is actually an image (additional security check)
                if (!$this->isValidImageFile($file)) {
                    $validator->errors()->add('image', 'The uploaded file does not appear to be a valid image.');
                }
            }
        });
    }

    /**
     * Check if the uploaded file is a valid image
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return bool
     */
    protected function isValidImageFile($file): bool
    {
        try {
            // For PDF files, we don't need to check image properties
            if (strtolower($file->getClientOriginalExtension()) === 'pdf') {
                return true;
            }

            // For image files, try to get image info
            $imageInfo = getimagesize($file->getPathname());
            return $imageInfo !== false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}