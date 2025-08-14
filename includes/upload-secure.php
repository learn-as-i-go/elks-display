<?php
require_once 'security.php';

function handleSecureImageUpload($file, $subfolder = 'general') {
    // Security validation first
    $security_errors = validateUploadSecurity($file);
    if (!empty($security_errors)) {
        logSecurityEvent('file_upload_security_violation', [
            'errors' => $security_errors,
            'filename' => $file['name'] ?? 'unknown',
            'size' => $file['size'] ?? 0
        ]);
        return ['success' => false, 'error' => implode(', ', $security_errors)];
    }
    
    // Sanitize subfolder name
    $subfolder = preg_replace('/[^a-zA-Z0-9_-]/', '', $subfolder);
    if (empty($subfolder)) {
        $subfolder = 'general';
    }
    
    // Define the correct upload path structure
    $base_upload_dir = __DIR__ . '/../uploads/';
    $upload_dir = $base_upload_dir . $subfolder . '/';
    
    // Ensure upload directory exists with secure permissions
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
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
    
    // Generate secure filename
    $original_name = pathinfo($file['name'], PATHINFO_FILENAME);
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Clean filename - remove special characters but preserve meaningful names
    $safe_name = preg_replace('/[^a-zA-Z0-9_\-]/', '', $original_name);
    $safe_name = substr($safe_name, 0, 50); // Limit length
    
    // Add timestamp and random component to prevent conflicts and directory traversal
    $timestamp = date('Y-m-d_H-i-s');
    $random = bin2hex(random_bytes(4));
    $filename = $safe_name . '_' . $timestamp . '_' . $random . '.' . $extension;
    
    $full_path = $upload_dir . $filename;
    $relative_path = 'uploads/' . $subfolder . '/' . $filename;
    
    // Additional security check - ensure path is within upload directory
    $real_upload_dir = realpath($upload_dir);
    $real_full_path = realpath(dirname($full_path)) . '/' . basename($full_path);
    
    if (strpos($real_full_path, $real_upload_dir) !== 0) {
        logSecurityEvent('path_traversal_attempt', ['attempted_path' => $full_path]);
        return ['success' => false, 'error' => 'Invalid file path'];
    }
    
    error_log("Attempting to move file from " . $file['tmp_name'] . " to " . $full_path);
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $full_path)) {
        error_log("File uploaded successfully to: $full_path");
        
        // Set secure file permissions
        chmod($full_path, 0644);
        
        // Verify file was actually created and is still valid
        if (file_exists($full_path) && getimagesize($full_path)) {
            $file_size = filesize($full_path);
            error_log("Upload verified - file exists with size: $file_size bytes");
            
            // Resize images for optimal display and security
            if ($subfolder === 'presidents' || $subfolder === 'board') {
                // Portrait images - resize to 600px width for optimal display
                $resize_result = resizeImageSecure($full_path, 600, 800, 90);
                if (!$resize_result) {
                    error_log("Warning: Image resize failed for $full_path");
                }
            } else {
                // Other images - general resize if too large
                $image_info = getimagesize($full_path);
                if ($image_info && ($image_info[0] > 1200 || $image_info[1] > 1200)) {
                    $resize_result = resizeImageSecure($full_path, 1200, 1200, 90);
                    if (!$resize_result) {
                        error_log("Warning: Image resize failed for $full_path");
                    }
                }
            }
            
            logSecurityEvent('file_upload_success', [
                'filename' => $filename,
                'original_name' => $original_name,
                'size' => $file_size,
                'subfolder' => $subfolder
            ]);
            
            return [
                'success' => true, 
                'path' => $relative_path,
                'filename' => $filename,
                'original_name' => $original_name,
                'full_path' => $full_path,
                'size' => $file_size
            ];
        } else {
            error_log("Upload failed - file does not exist or is invalid after move");
            // Clean up the invalid file
            if (file_exists($full_path)) {
                unlink($full_path);
            }
            return ['success' => false, 'error' => 'File was not created properly after upload'];
        }
    } else {
        error_log("move_uploaded_file failed from " . $file['tmp_name'] . " to " . $full_path);
        return ['success' => false, 'error' => 'Failed to save uploaded file'];
    }
}

function deleteImageSecure($relative_path) {
    if (!$relative_path) return true;
    
    // Validate path to prevent directory traversal
    if (strpos($relative_path, '..') !== false || strpos($relative_path, '/') !== 0) {
        $relative_path = ltrim($relative_path, '/');
    }
    
    $full_path = __DIR__ . '/../' . $relative_path;
    
    // Ensure the file is within the uploads directory
    $real_uploads_dir = realpath(__DIR__ . '/../uploads/');
    $real_file_path = realpath($full_path);
    
    if ($real_file_path && strpos($real_file_path, $real_uploads_dir) === 0) {
        if (file_exists($full_path)) {
            $result = unlink($full_path);
            error_log("Deleted image: $full_path - " . ($result ? 'success' : 'failed'));
            
            if ($result) {
                logSecurityEvent('file_deleted', ['path' => $relative_path]);
            }
            
            return $result;
        }
    } else {
        logSecurityEvent('file_deletion_security_violation', ['attempted_path' => $relative_path]);
        error_log("Security violation: Attempted to delete file outside uploads directory: $relative_path");
    }
    
    return true;
}

function resizeImageSecure($source_path, $max_width = 800, $max_height = 600, $quality = 85) {
    if (!file_exists($source_path)) {
        error_log("Resize failed - source file does not exist: $source_path");
        return false;
    }
    
    // Validate that this is actually an image file
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
    $source = null;
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
            if (function_exists('imagecreatefromwebp')) {
                $source = imagecreatefromwebp($source_path);
            }
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
    if (!$destination) {
        imagedestroy($source);
        return false;
    }
    
    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }
    
    // Resize image
    $resize_success = imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);
    
    if (!$resize_success) {
        imagedestroy($source);
        imagedestroy($destination);
        return false;
    }
    
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
            if (function_exists('imagewebp')) {
                $result = imagewebp($destination, $source_path, $quality);
            }
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

// Helper function to ensure upload directories exist with secure permissions
function ensureSecureUploadDirectories() {
    $base_dir = __DIR__ . '/../uploads/';
    $subdirs = ['presidents', 'board', 'events', 'general'];
    
    foreach ($subdirs as $subdir) {
        $dir_path = $base_dir . $subdir;
        if (!file_exists($dir_path)) {
            if (mkdir($dir_path, 0755, true)) {
                error_log("Created upload directory: $dir_path");
                
                // Create .htaccess to prevent direct PHP execution
                $htaccess_content = "# Prevent PHP execution in upload directory\n";
                $htaccess_content .= "<Files *.php>\n";
                $htaccess_content .= "    Order allow,deny\n";
                $htaccess_content .= "    Deny from all\n";
                $htaccess_content .= "</Files>\n";
                $htaccess_content .= "<Files *.phtml>\n";
                $htaccess_content .= "    Order allow,deny\n";
                $htaccess_content .= "    Deny from all\n";
                $htaccess_content .= "</Files>\n";
                
                file_put_contents($dir_path . '/.htaccess', $htaccess_content);
            }
        }
    }
}

// Call this function to ensure directories exist
ensureSecureUploadDirectories();
?>
