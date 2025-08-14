<?php
function handleImageUploadSimple($file, $upload_dir = 'uploads/') {
    // Simple, direct approach
    $upload_path = __DIR__ . '/../' . $upload_dir;
    
    // Create directory if it doesn't exist
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0775, true);
    }
    
    // Basic upload validation
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error code: ' . $file['error']];
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10MB limit
        return ['success' => false, 'error' => 'File too large'];
    }
    
    // Keep original filename but make it safe
    $original_name = $file['name'];
    $safe_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name);
    
    // Add timestamp only if file already exists
    $final_name = $safe_name;
    $counter = 1;
    while (file_exists($upload_path . $final_name)) {
        $info = pathinfo($safe_name);
        $final_name = $info['filename'] . '_' . $counter . '.' . $info['extension'];
        $counter++;
    }
    
    $destination = $upload_path . $final_name;
    
    // Simple move without processing
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        chmod($destination, 0644);
        return [
            'success' => true,
            'filepath' => $upload_dir . $final_name,
            'filename' => $final_name,
            'original_name' => $original_name
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

function simpleResize($filepath, $max_width = 800, $max_height = 600) {
    // Skip resize for now to speed things up
    return true;
}
?>
