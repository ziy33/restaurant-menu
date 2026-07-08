<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(0);

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => '未授权访问']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => '仅支持 POST 请求']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => '未选择图片文件']);
    exit;
}

$file = $_FILES['image'];

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => '文件超过 php.ini 的 upload_max_filesize 限制',
        UPLOAD_ERR_FORM_SIZE => '文件超过表单的 MAX_FILE_SIZE 限制',
        UPLOAD_ERR_PARTIAL => '文件仅部分上传',
        UPLOAD_ERR_NO_FILE => '未选择文件',
        UPLOAD_ERR_NO_TMP_DIR => '缺少临时文件夹',
        UPLOAD_ERR_CANT_WRITE => '写入磁盘失败',
        UPLOAD_ERR_EXTENSION => 'PHP 扩展阻止了文件上传'
    ];
    $errorMsg = $errorMessages[$file['error']] ?? '上传失败';
    echo json_encode(['success' => false, 'error' => $errorMsg]);
    exit;
}

// Validate file size (5MB max)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => '文件大小不能超过 5MB']);
    exit;
}

// Get file extension
$originalName = $file['name'];
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Validate by extension (primary check - works without fileinfo extension)
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($extension, $allowedExtensions)) {
    echo json_encode(['success' => false, 'error' => '不支持的文件类型，仅支持 JPG、PNG、GIF、WebP']);
    exit;
}

// Validate MIME type if fileinfo extension is available (secondary check)
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (class_exists('finfo')) {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => '不支持的文件类型，仅支持 JPG、PNG、GIF、WebP']);
        exit;
    }
} else {
    // Fallback: check MIME type from $_FILES
    $mimeType = $file['type'] ?? '';
    if (!empty($mimeType) && !in_array($mimeType, $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => '不支持的文件类型，仅支持 JPG、PNG、GIF、WebP']);
        exit;
    }
}

// Create uploads directory if it doesn't exist
$uploadDir = dirname(__DIR__) . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate unique filename
$uniqueFilename = uniqid('dish_', true) . '.' . $extension;
$uploadPath = $uploadDir . $uniqueFilename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    echo json_encode([
        'success' => true,
        'filename' => $uniqueFilename,
        'original_name' => $originalName,
        'size' => $file['size'],
        'type' => $mimeType ?? ''
    ]);
} else {
    echo json_encode(['success' => false, 'error' => '保存文件失败，请检查 uploads 目录权限']);
}
