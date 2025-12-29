// ORIGINAL IMAGE SIZE
$origWidth  = imagesx($src);
$origHeight = imagesy($src);

// FIXED WIDTH
$newWidth = 400;

// CALCULATE HEIGHT PROPORTIONALLY
$newHeight = ($origHeight / $origWidth) * $newWidth;

// CREATE SMALL IMAGE
$smallImg = imagecreatetruecolor($newWidth, $newHeight);

imagecopyresampled(
    $smallImg,
    $src,
    0, 0, 0, 0,
    $newWidth,
    $newHeight,
    $origWidth,
    $origHeight
);

// SAVE SMALL IMAGE
imagejpeg($smallImg, $uploadPath.$smallName, 90);
