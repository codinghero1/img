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


<?php
$conn = mysqli_connect("localhost","root","","cms");

$id = $_GET['id'] ?? '';
$edit = false;
$oldSmall = $oldBig = '';

if($id){
    $edit = true;
    $res = mysqli_query($conn,"SELECT * FROM gallery WHERE id=$id");
    $row = mysqli_fetch_assoc($res);
    $oldSmall = $row['gallery_small_img'];
    $oldBig   = $row['gallery_big_img'];
}

if(isset($_POST['save'])){

    $hasImage = !empty($_FILES['image']['name']);
    $uploadPath = "uploads/";

    if($hasImage){

        // delete old images
        if($edit){
            @unlink($uploadPath.$oldSmall);
            @unlink($uploadPath.$oldBig);
        }

        $bigName   = "gallery_big_img".($edit?$id:time()).".jpg";
        $smallName = "gallery_small_img".($edit?$id:time()).".jpg";

        $srcImg = imagecreatefromjpeg($_FILES['image']['tmp_name']);

        // BIG IMAGE
        imagejpeg($srcImg,$uploadPath.$bigName,90);

        // SMALL IMAGE (400px proportional)
        $ow = imagesx($srcImg);
        $oh = imagesy($srcImg);

        $nw = 400;
        $nh = ($oh/$ow)*$nw;

        $thumb = imagecreatetruecolor($nw,$nh);

        imagecopyresampled(
            $thumb,$srcImg,
            0,0,0,0,
            $nw,$nh,
            $ow,$oh
        );

        imagejpeg($thumb,$uploadPath.$smallName,90);
    }

    if($edit){

        if($hasImage){
            mysqli_query($conn,"
                UPDATE gallery SET
                gallery_small_img='$smallName',
                gallery_big_img='$bigName'
                WHERE id=$id
            ");
        }

    } else {

        mysqli_query($conn,"
            INSERT INTO gallery(gallery_small_img,gallery_big_img)
            VALUES('$smallName','$bigName')
        ");
    }

    header("Location: gallery_list.php");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Gallery Form</title>
</head>
<body>

<h2><?= $edit ? 'Update' : 'Add' ?> Image</h2>

<form method="post" enctype="multipart/form-data">

    <input type="file" name="image" <?= $edit?'':'required' ?>>
    <br><br>

    <?php if($edit){ ?>
        <img src="uploads/<?= $oldSmall ?>" width="150">
        <br><br>
    <?php } ?>

    <button name="save"><?= $edit?'Update':'Upload' ?></button>
</form>

</body>
</html>

