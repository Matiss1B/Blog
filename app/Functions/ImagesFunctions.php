<?php
namespace App\Functions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImagesFunctions {
    public function compress($source, $quality){
        $info = getimagesize($source);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($source);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($source);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($source);
        } else {
            return false; // Unsupported image format
        }

        ob_start(); // Start output buffering
        imagejpeg($image, null, $quality); // Output the compressed image to the buffer
        imagedestroy($image);
        $compressedImage = ob_get_clean();
        $destinationPath = 'images/' . Str::random(60) . '.jpg'; // Replace with the desired destination path within the disk
        Storage::disk('public')->put($destinationPath, $compressedImage);// Get the buffer content and clean the buffer
        return $destinationPath;// Get the buffer content and clean the buffer
    }
}
