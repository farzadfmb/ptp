<?php

class FileHelper
{
    /**
     * Upload file
     */
    public static function uploadFile($file, $uploadDir = 'uploads/', $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'])
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'فایلی انتخاب نشده است'];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'خطا در آپلود فایل'];
        }

        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension'] ?? '');

        if (!in_array($extension, $allowedTypes)) {
            return ['success' => false, 'error' => 'نوع فایل مجاز نیست'];
        }

        // Create upload directory if it doesn't exist
        $fullUploadDir = __DIR__ . '/../../public/' . $uploadDir;
        if (!is_dir($fullUploadDir)) {
            mkdir($fullUploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $destination = $fullUploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return [
                'success' => true,
                'filename' => $filename,
                'path' => $uploadDir . $filename,
                'size' => $file['size'],
                'type' => $file['type']
            ];
        }

        return ['success' => false, 'error' => 'خطا در ذخیره فایل'];
    }

    /**
     * Delete file
     */
    public static function deleteFile($filePath)
    {
        $fullPath = __DIR__ . '/../../public/' . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }

    /**
     * Get file size in readable format
     */
    public static function getFileSize($filePath)
    {
        $fullPath = __DIR__ . '/../../public/' . $filePath;
        
        if (file_exists($fullPath)) {
            $bytes = filesize($fullPath);
            return UtilityHelper::formatFileSize($bytes);
        }
        
        return false;
    }

    /**
     * Check if file exists
     */
    public static function fileExists($filePath)
    {
        $fullPath = __DIR__ . '/../../public/' . $filePath;
        return file_exists($fullPath);
    }

    /**
     * Get file extension
     */
    public static function getFileExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Validate image file
     */
    public static function isValidImage($file)
    {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return false;
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        $imageInfo = getimagesize($file['tmp_name']);
        return $imageInfo !== false;
    }

    /**
     * Resize image
     */
    public static function resizeImage($sourcePath, $destinationPath, $maxWidth, $maxHeight, $quality = 80)
    {
        $fullSourcePath = __DIR__ . '/../../public/' . $sourcePath;
        $fullDestinationPath = __DIR__ . '/../../public/' . $destinationPath;

        if (!file_exists($fullSourcePath)) {
            return false;
        }

        $imageInfo = getimagesize($fullSourcePath);
        if (!$imageInfo) {
            return false;
        }

        list($originalWidth, $originalHeight, $imageType) = $imageInfo;

        // Calculate new dimensions
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);

        // Create new image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Handle transparency for PNG and GIF
        if ($imageType == IMAGETYPE_PNG || $imageType == IMAGETYPE_GIF) {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Load source image
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($fullSourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($fullSourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = imagecreatefromgif($fullSourcePath);
                break;
            default:
                return false;
        }

        // Resize
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

        // Create destination directory if it doesn't exist
        $destinationDir = dirname($fullDestinationPath);
        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0755, true);
        }

        // Save resized image
        $result = false;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $result = imagejpeg($newImage, $fullDestinationPath, $quality);
                break;
            case IMAGETYPE_PNG:
                $result = imagepng($newImage, $fullDestinationPath);
                break;
            case IMAGETYPE_GIF:
                $result = imagegif($newImage, $fullDestinationPath);
                break;
        }

        // Clean up memory
        imagedestroy($sourceImage);
        imagedestroy($newImage);

        return $result;
    }

    /**
     * Generate thumbnail
     */
    public static function generateThumbnail($sourcePath, $thumbnailPath, $size = 150, $quality = 80)
    {
        return self::resizeImage($sourcePath, $thumbnailPath, $size, $size, $quality);
    }

    /**
     * Get MIME type
     */
    public static function getMimeType($filePath)
    {
        $fullPath = __DIR__ . '/../../public/' . $filePath;
        
        if (file_exists($fullPath)) {
            return mime_content_type($fullPath);
        }
        
        return false;
    }
}