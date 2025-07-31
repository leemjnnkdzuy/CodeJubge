<?php
class AvatarHelper {
    public static function getDefaultAvatarBase64() {
        $defaultAvatarPath = PUBLIC_PATH . '/assets/default_avatar.png';
        
        if (!file_exists($defaultAvatarPath)) {
            return self::generateSimpleAvatar();
        }
        
        $imageData = file_get_contents($defaultAvatarPath);
        $imageInfo = getimagesize($defaultAvatarPath);
        
        if ($imageInfo && isset($imageInfo['mime'])) {
            $mimeType = $imageInfo['mime'];
            return 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        }
        
        return self::generateSimpleAvatar();
    }

    private static function isGDEnabled() {
        return extension_loaded('gd');
    }

    public static function generateSimpleAvatar($initials = 'U', $size = 150) {
        if (!self::isGDEnabled()) {
            return self::generateSVGAvatar($initials, $size);
        }
        
        $image = imagecreate($size, $size);
        
        $bgColors = [
            [52, 152, 219],
            [155, 89, 182],
            [26, 188, 156],
            [241, 196, 15],
            [230, 126, 34],
            [231, 76, 60],
            [46, 204, 113],
            [149, 165, 166]
        ];
        
        $randomColor = $bgColors[array_rand($bgColors)];
        $bgColor = imagecolorallocate($image, $randomColor[0], $randomColor[1], $randomColor[2]);
        $textColor = imagecolorallocate($image, 255, 255, 255);
        
        imagefill($image, 0, 0, $bgColor);
        
        $fontSize = $size / 4;
        $font = 5;
        
        $textWidth = imagefontwidth($font) * strlen($initials);
        $textHeight = imagefontheight($font);
        $x = ($size - $textWidth) / 2;
        $y = ($size - $textHeight) / 2;
        
        imagestring($image, $font, $x, $y, strtoupper($initials), $textColor);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    private static function generateSVGAvatar($initials = 'U', $size = 150) {
        $colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#FFB347', '#87CEEB', '#98D8C8', '#F7DC6F'
        ];
        
        $bgColor = $colors[array_rand($colors)];
        $initials = strtoupper(substr($initials, 0, 2));
        
        $svg = '<?xml version="1.0" encoding="UTF-8"?>
        <svg width="' . $size . '" height="' . $size . '" xmlns="http://www.w3.org/2000/svg">
            <rect width="' . $size . '" height="' . $size . '" fill="' . $bgColor . '"/>
            <text x="50%" y="50%" 
                  font-family="Arial, sans-serif" 
                  font-size="' . ($size * 0.4) . '" 
                  fill="white" 
                  text-anchor="middle" 
                  dominant-baseline="central">' . $initials . '</text>
        </svg>';
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function processUploadedImage($file, $maxSize = 2048000) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            throw new Exception("File không hợp lệ");
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception("File quá lớn. Tối đa " . ($maxSize / 1024 / 1024) . "MB");
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $imageInfo = getimagesize($file['tmp_name']);
        
        if (!$imageInfo || !in_array($imageInfo['mime'], $allowedTypes)) {
            throw new Exception("Chỉ chấp nhận file ảnh (JPEG, PNG, GIF, WebP)");
        }
        
        if (!self::isGDEnabled()) {
            $imageData = file_get_contents($file['tmp_name']);
            return 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($imageData);
        }
        
        $resizedImage = self::resizeImage($file['tmp_name'], $imageInfo['mime']);
        
        return 'data:' . $imageInfo['mime'] . ';base64,' . base64_encode($resizedImage);
    }
    
    private static function resizeImage($imagePath, $mimeType, $maxWidth = 300, $maxHeight = 300) {
        if (!self::isGDEnabled()) {
            throw new Exception("GD extension không có sẵn để xử lý ảnh");
        }
        
        switch ($mimeType) {
            case 'image/jpeg':
                $sourceImage = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $sourceImage = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $sourceImage = imagecreatefromgif($imagePath);
                break;
            case 'image/webp':
                $sourceImage = imagecreatefromwebp($imagePath);
                break;
            default:
                throw new Exception("Định dạng ảnh không được hỗ trợ");
        }
        
        $originalWidth = imagesx($sourceImage);
        $originalHeight = imagesy($sourceImage);
        
        $ratio = min($maxWidth / $originalWidth, $maxHeight / $originalHeight);
        $newWidth = intval($originalWidth * $ratio);
        $newHeight = intval($originalHeight * $ratio);
        
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        
        if ($mimeType == 'image/png' || $mimeType == 'image/gif') {
            imagealphablending($newImage, false);
            imagesavealpha($newImage, true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefill($newImage, 0, 0, $transparent);
        }
        
        imagecopyresampled($newImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        ob_start();
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($newImage, null, 85);
                break;
            case 'image/png':
                imagepng($newImage, null, 6);
                break;
            case 'image/gif':
                imagegif($newImage);
                break;
            case 'image/webp':
                imagewebp($newImage, null, 85);
                break;
        }
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($sourceImage);
        imagedestroy($newImage);
        
        return $imageData;
    }

    public static function getInitials($fullName) {
        if (empty($fullName)) {
            return 'U';
        }
        
        $words = explode(' ', trim($fullName));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) break;
            }
        }
        
        return empty($initials) ? 'U' : $initials;
    }

    public static function validateBase64Image($base64String) {
        if (!preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $base64String)) {
            return false;
        }
        
        $data = explode(',', $base64String, 2);
        if (count($data) !== 2) {
            return false;
        }
        
        $decoded = base64_decode($data[1], true);
        if ($decoded === false) {
            return false;
        }
        
        $imageInfo = getimagesizefromstring($decoded);
        return $imageInfo !== false;
    }
    
    public static function base64ToImageSrc($base64String) {
        if (empty($base64String)) {
            return self::getDefaultAvatarBase64();
        }
        
        if (self::validateBase64Image($base64String)) {
            return $base64String;
        }
        
        return self::getDefaultAvatarBase64();
    }

    public static function defaultAvatarExists() {
        $defaultAvatarPath = PUBLIC_PATH . '/assets/default_avatar.png';
        return file_exists($defaultAvatarPath);
    }
    
    public static function getDefaultAvatarPath() {
        return PUBLIC_PATH . '/assets/default_avatar.png';
    }
}
