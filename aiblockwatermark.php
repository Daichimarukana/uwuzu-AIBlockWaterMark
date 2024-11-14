<?php 
function AIBlockWaterMark($path, $userid){
    require("assets/img/base64img.php");
    $fontPath = __DIR__.'/assets/font/LINESeedJP_A_TTF_Bd.ttf'; // フォントファイルのパス

    if(file_exists($fontPath) == true && isset($path) && file_exists($path) == true && isset($userid)){
        if (false == strpos($userid, '@')) {
            $userid = "@".$userid;
        }
        $finfo = new finfo();
        $tmp_ext = $finfo->file($path, FILEINFO_MIME_TYPE);
        $safe_img_mime = array(
            "image/jpeg",
            "image/png",
            "image/webp",
            "image/bmp"
        );
        if(in_array($tmp_ext,$safe_img_mime)){
            switch ($tmp_ext) {
                case "image/jpeg":
                    $baseImage = imagecreatefromjpeg($path); 
                    break;
                case "image/png":
                    $baseImage = imagecreatefrompng($path); 
                    break;
                case "image/webp":
                    $baseImage = imagecreatefromwebp($path); 
                    break;
                case "image/bmp":
                    $baseImage = imagecreatefrombmp($path); 
                    break;
                default:
                    return false;
                    exit;
                    break;
            }

            // 透かし画像の読み込み
            $watermark = imagecreatefromstring(base64_decode($watermark_img_base64));

            $opacity = 90;

            imagealphablending($baseImage, true); 
            imagesavealpha($baseImage, true); 

            $baseWidth = imagesx($baseImage); 
            $baseHeight = imagesy($baseImage); 

            $wmWidth = imagesx($watermark); 
            $wmHeight = imagesy($watermark); 

            $aspectRatio = $wmWidth / $wmHeight; 

            $newWmWidth = $baseWidth / 4; 
            $newWmHeight = (int)($newWmWidth / $aspectRatio); 

            $resizedWatermark = imagecreatetruecolor($newWmWidth, $newWmHeight); 
            imagealphablending($resizedWatermark, false); 
            imagesavealpha($resizedWatermark, true); 

            imagecopyresampled($resizedWatermark, $watermark, 0, 0, 0, 0, $newWmWidth, $newWmHeight, $wmWidth, $wmHeight); 

            $margeRight = 10; 
            $margeBottom = 10; 
            $xPosition = $baseWidth - $newWmWidth - $margeRight; 
            $yPosition = $baseHeight - $newWmHeight - $margeBottom; 

            // リサイズした透かし画像に透明度を適用 
            for ($x = 0; $x < $newWmWidth; $x++) {
                for ($y = 0; $y < $newWmHeight; $y++) {
                    if ($x >= imagesx($resizedWatermark) || $y >= imagesy($resizedWatermark)) {
                        imagedestroy($baseImage); 
                        imagedestroy($watermark); 
                        imagedestroy($resizedWatermark); 
                        return;
                        exit;
                    }
                    $rgba = imagecolorat($resizedWatermark, $x, $y);
                    $alpha = ($rgba & 0x7F000000) >> 24;
                    $newAlpha = min(127, $alpha + $opacity); // 元のアルファ値に透明度を追加
                    $color = imagecolorallocatealpha(
                        $resizedWatermark,
                        ($rgba >> 16) & 0xFF,
                        ($rgba >> 8) & 0xFF,
                        $rgba & 0xFF,
                        $newAlpha
                    );
                    imagesetpixel($resizedWatermark, $x, $y, $color);
                }
            }

            $fontSize = 11 * 0.1 * ( $newWmWidth / 21 ); // フォントサイズ
            $textColor = imagecolorallocatealpha($resizedWatermark, 0, 0, 0, 80); // 黒色のテキスト、40の透明度
            $textX = $newWmWidth - $newWmWidth * 0.94; // テキストの位置調整
            $textY = $newWmHeight - $newWmHeight * 0.06; // テキストの位置調整
            imagettftext($resizedWatermark, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $userid);

            imagecopy($baseImage, $resizedWatermark, $xPosition, $yPosition, 0, 0, $newWmWidth, $newWmHeight); 

            // 画像データを出力 
            switch ($tmp_ext) {
                case "image/jpeg":
                    imagejpeg($baseImage, $path); 
                    break;
                case "image/png":
                    imagepng($baseImage, $path); 
                    break;
                case "image/webp":
                    imagewebp($baseImage, $path); 
                    break;
                case "image/bmp":
                    imagebmp($baseImage, $path); 
                    break;
                default:
                    return false;
                    exit;
                    break;
            }

            // メモリを解放 
            imagedestroy($baseImage); 
            imagedestroy($watermark); 
            imagedestroy($resizedWatermark); 

            return true;
        }else{
            return false;
        }
    }else{
        return false;
    }
}

?>
