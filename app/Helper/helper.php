<?php

public function uploadBase64Image($base64Image)
    {
        $decoder = new Base64ImageDecoder($base64Image, $allowedMimeTypes = ['jpg', 'png', 'gif', 'jpeg']);

        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        $decodedImage = $decoder->getDecodedContent();
        if (strlen($decodedImage) > 2 * 1024 * 1024) {
            throw new \Exception('Ukuran gambar maksimal 2MB');
        }

        $format = $decoder->getFormat();
        $image = Str::random(10) . '.' . $format;
        Storage::disk('public')->put($image, $decodedImage);
        return $image;
    }