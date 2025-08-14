<?php
function handleImageUpload($file, $subfolder = 'general') {
    // Debug logging
    error_log("Upload attempt - Subfolder: $subfolder, File: " . print_r($file, true));
    
    // Define the correct upload path structure
    $base_upload_dir = __DIR__ . '/../uploads/';
    $upload_dir = $base_upload_dir . $subfolder . '/';
    
    // Ensure upload directory exists
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0775, true)) {
            error_log("Failed to create upload directory: $upload_dir");
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
        error_log("Created upload directory: $upload_dir");
    }
    
    // Check if directory is writable
    if (!is_writable($upload_dir)) {
        error_log("Upload directory not writable: $upload_dir");
        return ['success' => false, 'error' => 'Upload directory is not writable'];
    }
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
            UPLOAD_ERR_PARTIAL => 'File upload incomplete',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
        
        $error = $error_messages[$file['error']] ?? 'Unknown upload error (code: ' . $file['error'] . ')';
        error_log("Upload error: $error");
        return ['success' => false, 'error' => $error];
    }
    
    // Check if temp file exists
    if (!file_exists($file['tmp_name'])) {
        error_log("Temp file does not exist: " . $file['tmp_name']);
        return ['success' => false, 'error' => 'Temporary file not found'];
    }
    
    // Validate file type
    $file_info = getimagesize($file['tmp_name']);
    if (!$file_info) {
        error_log("Invalid image file: " . $file['tmp_name']);
        return ['success' => false, 'error' => 'Invalid image file'];
    }
    
    $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP];
    if (!in_array($file_info[2], $allowed_types)) {
        error_log("Unsupported image type: " . $file_info[2]);
        return ['success' => false, 'error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.'];
    }
    
    // Validate file size (max 10MB for high-quality photos)
    if ($file['size'] > 10 * 1024 * 1024) {
        error_log("File too large: " . $file['size'] . " bytes");
        return ['success' => false, 'error' => 'File too large. Maximum size is 10MB.'];
    }
    
    // Preserve original filename but make it safe
    $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Clean filename - remove special characters but preserve meaningful names
    $safe_name = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $original_name);
    $safe_name = preg_replace('/\s+/', '_', trim($safe_name));
    $safe_name = substr($safe_name, 0, 50); // Limit length
    
    // Add timestamp to prevent conflicts while keeping readable name
    $timestamp = date('Y-m-d_H-i-s');
    $filename = $safe_name . '_' . $timestamp . '.' . $extension;
    
    $full_path = $upload_dir . $filename;
    $relative_path = 'uploads/' . $subfolder . '/' . $filename;
    
    error_log("Attempting to move file from " . $file['tmp_name'] . " to " . $full_path);
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $full_path)) {
        error_log("File uploaded successfully to: $full_path");
        
        // Set file permissions
        chmod($full_path, 0644);
        
        // Verify file was actually created
        if (file_exists($full_path)) {
            $file_size = filesize($full_path);
            error_log("Upload verified - file exists with size: $file_size bytes");
            
            // Resize images for optimal display
            if ($subfolder === 'presidents' || $subfolder === 'board') {
                // Portrait images - resize to 600px width for optimal display
                resizeImage($full_path, 600, 800, 90);
                error_log("Portrait image resized to 600px width for display optimization");
            } else {
                // Other images - general resize if too large
                if ($file_info[0] > 1200 || $file_info[1] > 1200) {
                    resizeImage($full_path, 1200, 1200, 90);
                    error_log("Image resized for optimization");
                }
            }
            
            return [
                'success' => true, 
                'path' => $relative_path,
                'filename' => $filename,
                'original_name' => $original_name,
                'full_path' => $full_path,
                'size' => $file_size
            ];
        } else {
            error_log("Upload failed - file does not exist after move");
            return ['success' => false, 'error' => 'File was not created after upload'];
        }
    } else {
        error_log("move_uploaded_file failed from " . $file['tmp_name'] . " to " . $full_path);
        return ['success' => false, 'error' => 'Failed to save uploaded file'];
    }
}

function deleteImage($relative_path) {
    if (!$relative_path) return true;
    
    $full_path = __DIR__ . '/../' . $relative_path;
    if (file_exists($full_path)) {
        $result = unlink($full_path);
        error_log("Deleted image: $full_path - " . ($result ? 'success' : 'failed'));
        return $result;
    }
    return true;
}

function resizeImage($source_path, $max_width = 800, $max_height = 600, $quality = 85) {
    if (!file_exists($source_path)) {
        error_log("Resize failed - source file does not exist: $source_path");
        return false;
    }
    
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        error_log("Resize failed - invalid image: $source_path");
        return false;
    }
    
    $original_width = $image_info[0];
    $original_height = $image_info[1];
    $image_type = $image_info[2];
    
    // Skip resize if image is already smaller
    if ($original_width <= $max_width && $original_height <= $max_height) {
        error_log("Resize skipped - image already small enough: {$original_width}x{$original_height}");
        return true;
    }
    
    // Calculate new dimensions maintaining aspect ratio
    $ratio = min($max_width / $original_width, $max_height / $original_height);
    $new_width = round($original_width * $ratio);
    $new_height = round($original_height * $ratio);
    
    error_log("Resizing from {$original_width}x{$original_height} to {$new_width}x{$new_height}");
    
    // Create image resource based on type
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($source_path);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($source_path);
            break;
        default:
            error_log("Resize failed - unsupported image type: $image_type");
            return false;
    }
    
    if (!$source) {
        error_log("Resize failed - could not create image resource");
        return false;
    }
    
    // Create new image
    $destination = imagecreatetruecolor($new_width, $new_height);
    
    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    
    // Save resized image
    $result = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($destination, $source_path, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($destination, $source_path);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($destination, $source_path);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($destination, $source_path, $quality);
            break;
    }
    
    // Clean up memory
    imagedestroy($source);
    imagedestroy($destination);
    
    if ($result) {
        error_log("Resize completed successfully");
    } else {
        error_log("Resize failed during save");
    }
    
    return $result;
}

// Helper function to ensure upload directories exist
function ensureUploadDirectories() {
    $base_dir = __DIR__ . '/../uploads/';
    $subdirs = ['presidents', 'board', 'events'];
    
    foreach ($subdirs as $subdir) {
        $dir_path = $base_dir . $subdir;
        if (!file_exists($dir_path)) {
            mkdir($dir_path, 0775, true);
            error_log("Created upload directory: $dir_path");
        }
    }
}

// Call this function to ensure directories exist
ensureUploadDirectories();
?>
