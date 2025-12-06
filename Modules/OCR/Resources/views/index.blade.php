<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OCR - Text Recognition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }
        .upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }
        .result-area {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            min-height: 200px;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .loading {
            display: none;
        }
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-eye me-2"></i>OCR - 文字识别
                        </h3>
                        @if($isTesseractAvailable)
                            <span class="badge bg-success">Tesseract {{ $tesseractVersion ?? 'Available' }}</span>
                        @else
                            <span class="badge bg-danger">Tesseract Not Available</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @if(!$isTesseractAvailable)
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Tesseract OCR is not available on this system. Please install Tesseract to use this feature.
                            </div>
                        @else
                            <form id="ocrForm" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="upload-area" id="uploadArea">
                                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                            <h5>拖拽图片到这里或点击选择文件</h5>
                                            <p class="text-muted">
                                                支持格式: {{ implode(', ', $supportedFormats) }}
                                                <br>最大文件大小: {{ number_format(config('ocr.max_file_size', 10485760) / 1024 / 1024, 1) }}MB
                                            </p>
                                            <input type="file" id="imageFile" name="image" accept="image/*,.pdf" style="display: none;">
                                            <button type="button" class="btn btn-primary" onclick="document.getElementById('imageFile').click()">
                                                <i class="fas fa-folder-open me-2"></i>选择文件
                                            </button>
                                        </div>
                                        <div id="imagePreview" style="display: none;">
                                            <img id="previewImg" class="preview-image" alt="Preview">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="language" class="form-label">识别语言</label>
                                            <select class="form-select" id="language" name="language">
                                                @foreach($availableLanguages as $lang)
                                                    <option value="{{ $lang }}" {{ $lang === 'eng' ? 'selected' : '' }}>
                                                        {{ $lang === 'eng' ? 'English' : ($lang === 'chi_sim' ? '简体中文' : ($lang === 'chi_tra' ? '繁体中文' : $lang)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100" id="processBtn">
                                            <i class="fas fa-magic me-2"></i>开始识别
                                        </button>
                                        <div class="loading text-center mt-3">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Processing...</span>
                                            </div>
                                            <p class="mt-2">正在识别文字...</p>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <div class="result-area" id="resultArea" style="display: none;">
                                <h5><i class="fas fa-file-alt me-2"></i>识别结果</h5>
                                <div id="resultStats" class="mb-3"></div>
                                <textarea id="resultText" class="form-control" rows="10" placeholder="识别的文字将显示在这里..."></textarea>
                                <div class="mt-3">
                                    <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard()">
                                        <i class="fas fa-copy me-2"></i>复制文本
                                    </button>
                                    <button type="button" class="btn btn-outline-success" onclick="downloadText()">
                                        <i class="fas fa-download me-2"></i>下载文本
                                    </button>
                                </div>
                            </div>

                            <div id="errorArea" class="alert alert-danger" style="display: none;">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <span id="errorMessage"></span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const uploadArea = document.getElementById('uploadArea');
        const imageFile = document.getElementById('imageFile');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const ocrForm = document.getElementById('ocrForm');
        const processBtn = document.getElementById('processBtn');
        const loading = document.querySelector('.loading');
        const resultArea = document.getElementById('resultArea');
        const resultText = document.getElementById('resultText');
        const resultStats = document.getElementById('resultStats');
        const errorArea = document.getElementById('errorArea');
        const errorMessage = document.getElementById('errorMessage');

        // Drag and drop functionality
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                imageFile.files = files;
                previewImage(files[0]);
            }
        });

        // File input change
        imageFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                previewImage(e.target.files[0]);
            }
        });

        // Preview image
        function previewImage(file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Form submission
        ocrForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!imageFile.files.length) {
                showError('请选择一个图片文件');
                return;
            }

            const formData = new FormData(ocrForm);
            
            processBtn.disabled = true;
            loading.style.display = 'block';
            hideError();
            resultArea.style.display = 'none';

            try {
                const response = await fetch('{{ route("ocr.process") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showResult(data.data);
                } else {
                    showError(data.error || '处理失败');
                }
            } catch (error) {
                showError('网络错误: ' + error.message);
            } finally {
                processBtn.disabled = false;
                loading.style.display = 'none';
            }
        });

        function showResult(data) {
            resultText.value = data.text;
            resultStats.innerHTML = `
                <div class="row">
                    <div class="col-md-3"><strong>文件名:</strong> ${data.filename}</div>
                    <div class="col-md-3"><strong>语言:</strong> ${data.language}</div>
                    <div class="col-md-3"><strong>字符数:</strong> ${data.character_count}</div>
                    <div class="col-md-3"><strong>单词数:</strong> ${data.word_count}</div>
                </div>
            `;
            resultArea.style.display = 'block';
        }

        function showError(message) {
            errorMessage.textContent = message;
            errorArea.style.display = 'block';
        }

        function hideError() {
            errorArea.style.display = 'none';
        }

        function copyToClipboard() {
            resultText.select();
            document.execCommand('copy');
            
            // Show toast notification
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-white bg-success border-0 position-fixed top-0 end-0 m-3';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">文本已复制到剪贴板</div>
                </div>
            `;
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 3000);
        }

        function downloadText() {
            const text = resultText.value;
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'ocr_result.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>