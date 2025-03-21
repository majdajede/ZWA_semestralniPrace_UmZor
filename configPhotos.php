<?php
/**
 * Processes and saves uploaded photos.
 *
 * @param array $photos Array containing photo upload information.
 * @param array &$errors Reference to an array to store error messages.
 * @param string $photosDir Directory where photos will be saved.
 * @param string $prefix Prefix for the saved photo filenames.
 * @return array Array of saved photo filenames.
 *
 * The function performs the following steps:
 * 1. Checks if the number of uploaded photos exceeds the limit (4).
 * 2. Iterates through each uploaded photo and performs the following checks:
 *    - Validates if there was an upload error.
 *    - Validates the file size (max 4MB).
 *    - Validates the file type (only JPEG and PNG are allowed).
 * 3. Opens the photo as an image resource.
 * 4. Resizes the photo to a target size of 600x600 pixels, maintaining aspect ratio and centering the image.
 * 5. Saves the resized photo to the specified directory with the given prefix and appropriate file extension.
 * 6. Returns an array of saved photo filenames.
 */
function processPhotos($photos, &$errors, $photosDir, $prefix) {
    $savedPhotos = [];
    $countFiles = count($photos['name']);
    if ($countFiles > 4) {
        $errors[] = "Nahráváte příliš mnoho fotek (max. 4).";
        return [];
    }

    for ($i = 0; $i < $countFiles; $i++) {
        $tmpName = $photos['tmp_name'][$i];
        $name = $photos['name'][$i];
        $error = $photos['error'][$i];
        $type = $photos['type'][$i];
        $size = $photos['size'][$i];

        if ($error !== UPLOAD_ERR_OK) {
            $errors[] = "Chyba při nahrávání souboru '$name'.";
            continue;
        }

        if ($size > 4 * 1024 * 1024) {
            $errors[] = "Fotky, které se snažíte nahrát přesahují 4M. Zkuste nahrát prosím menší fotku.";
            continue;
        }

        if ($type !== 'image/jpeg' && $type !== 'image/png') {
            $errors[] = "Soubor '$name' není JPG ani PNG.";
            continue;
        }

        switch ($type) {
            case 'image/jpeg':
                $original = @imagecreatefromjpeg($tmpName);
                break;
            case 'image/png':
                $original = @imagecreatefrompng($tmpName);
                break;
            default:
                $original = null;
                break;
        }

        if (!$original) {
            $errors[] = "Soubor '$name' se nepodařilo otevřít jako obrázek.";
            continue;
        }

        $width = imagesx($original);
        $height = imagesy($original);

        $targetSize = 600;

        $newImage = imagecreatetruecolor($targetSize, $targetSize);

        $bgColor = imagecolorallocate($newImage, 255, 255, 255);
        imagefill($newImage, 0, 0, $bgColor);

        if ($width >= $height) {

            $newWidth = $targetSize;
            $newHeight = (int) round(($height * $targetSize) / $width);
            $offsetX = 0;

            $offsetY = (int) floor(($targetSize - $newHeight) / 2);
        } else {

            $newHeight = $targetSize;
            $newWidth = (int) round(($width * $targetSize) / $height);

            $offsetX = (int) floor(($targetSize - $newWidth) / 2);
            $offsetY = 0;
        }


        imagecopyresampled(
            $newImage,     
            $original,     
            $offsetX,      
            $offsetY,      
            0, 0,         
            $newWidth,     
            $newHeight,   
            $width,        
            $height      
        );

       $extension = ($type === 'image/jpeg') ? '.jpg' : '.png';
        $fileName = $prefix . '-' . $i . $extension;
        $destination = rtrim($photosDir, '/') . '/' . $fileName;

        if ($type === 'image/jpeg') {
            imagejpeg($newImage, $destination, 90);
        } else {
            imagepng($newImage, $destination);
        }

        imagedestroy($original);
        imagedestroy($newImage);

        $savedPhotos[] = $fileName;
    }

    return $savedPhotos;
}
