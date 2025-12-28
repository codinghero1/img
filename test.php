<?php
$conn = mysqli_connect("localhost","root","","cms");

if (isset($_POST['upload'])) {

    $img = $_FILES['image']['tmp_name'];

    // get next id
    $res = mysqli_query($conn, "SELECT MAX(id) as id FROM gallery");
    $row = mysqli_fetch_assoc($res);
    $nextId = $row['id'] + 1;

    $smallName = "gallery_small_img".$nextId.".jpg";
    $bigName   = "gallery_big_img".$nextId.".jpg";

    $uploadPath = "uploads/";

    // Load image
    $src = imagecreatefromjpeg($img);

    // BIG IMAGE (Original Size)
    imagejpeg($src, $uploadPath.$bigName, 90);

    // SMALL IMAGE (Resize)
    $smallWidth = 300;
    $smallHeight = 200;

    $smallImg = imagecreatetruecolor($smallWidth, $smallHeight);
    imagecopyresampled(
        $smallImg, $src,
        0,0,0,0,
        $smallWidth, $smallHeight,
        imagesx($src), imagesy($src)
    );

    imagejpeg($smallImg, $uploadPath.$smallName, 90);

    // Save to DB
    mysqli_query($conn, "
        INSERT INTO gallery (gallery_small_img, gallery_big_img)
        VALUES ('$smallName', '$bigName')
    ");

    echo "Image uploaded successfully!";
}
?>
