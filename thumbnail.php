<?php
function imagecreateauto($filename) {
    switch (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
        case 'jpeg':
        case 'jpg':
            return imagecreatefromjpeg($filename);
            break;
        case 'png':
            return imagecreatefrompng($filename);
            break;
        case 'gif':
            return imagecreatefromgif($filename);
            break;
        default:
            throw new InvalidArgumentException('File "' . htmlentities($filename) . '" is not valid jpg, png or gif image.');
            // will do something
            break;
    }
}

function calculateResizedHeight($originalWidth, $originalHeight, $newWidth) {
    $aspectRatio = $originalWidth / $originalHeight;
    $newHeight = round($newWidth / $aspectRatio);
    return $newHeight;
}

if ($_GET["m"] && $_GET["s"] && !empty($_GET["m"]) && !empty($_GET["p"])) {
    $positions = ["top", "bottom"];
    $position = in_array($_GET["p"] ?? "", $positions) ? $_GET["p"] : "bottom";
    $main = imagecreateauto(explode("plugins", __FILE__)[0]."uploads".$_GET["m"]);
    $ad = imagecreateauto(explode("plugins", __FILE__)[0]."uploads".$_GET["s"]);
    
    if ($main && $ad) {
        header("Content-Type: image/png");
        $main_width = 1200;
        $main_height = 630;

        $resizedMainImage = imagecreatetruecolor($main_width, $main_height);
        imagecopyresampled($resizedMainImage, $main, 0, 0, 0, 0, $main_width, $main_height, imagesx($main), imagesy($main));

        $ad_width = imagesx($ad);
        $ad_height = imagesy($ad);

        $newWidth = $main_width;
        $newHeight = calculateResizedHeight($ad_width, $ad_height, $newWidth);

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $ad, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($ad), imagesy($ad));

        $position_int = $position == "bottom" ? $main_height - $newHeight : 0;
        imagecopy($resizedMainImage, $resizedImage, 0, $position_int, 0, 0, $newWidth, $newHeight);
        
        imagepng($resizedMainImage);
    } else {
        http_response_code(404);
        echo "Error: Image not found!";
    }
} else {
    http_response_code(404);
    echo "Error: Required parameter missing!";
}
?>
