<?php
$conn = mysqli_connect("localhost","root","","cms");

$id = $_GET['id'] ?? '';
$edit = false;
$error = '';

// DEFAULT VALUES (for persist)
$image_name = '';
$status = '1';
$previewImg = '';
$oldSmall = $oldBig = '';

if($id){
    $edit = true;
    $res = mysqli_query($conn,"SELECT * FROM gallery WHERE id=$id");
    $row = mysqli_fetch_assoc($res);

    $image_name = $row['image_name'];
    $status     = $row['status'];
    $oldSmall   = $row['gallery_small_img'];
    $oldBig     = $row['gallery_big_img'];
    $previewImg = $oldSmall;
}

if(isset($_POST['save'])){

    // persist values
    $image_name = $_POST['image_name'];
    $status     = $_POST['status'];

    $hasImage = !empty($_FILES['image']['name']);
    $uploadPath = "uploads/";

    if($hasImage){

        $tmp  = $_FILES['image']['tmp_name'];
        $type = mime_content_type($tmp);
        list($width,$height) = getimagesize($tmp);

        if(!in_array($type,['image/jpeg','image/png'])){
            $error = "Only JPG and PNG images are allowed";
        }
        elseif($width < 600){
            $error = "Big image width must be at least 600px";
        }
        else{

            // remove old images
            if($edit){
                @unlink($uploadPath.$oldSmall);
                @unlink($uploadPath.$oldBig);
            }

            $imgId = $edit ? $id : time();

            $bigName   = "gallery_big_img".$imgId.".jpg";
            $smallName = "gallery_small_img".$imgId.".jpg";

            // create source
            $src = ($type=='image/png')
                ? imagecreatefrompng($tmp)
                : imagecreatefromjpeg($tmp);

            // BIG IMAGE (original)
            imagejpeg($src,$uploadPath.$bigName,90);

            // SMALL IMAGE (400px proportional)
            $newW = 400;
            $newH = ($height/$width)*$newW;

            $thumb = imagecreatetruecolor($newW,$newH);
            imagecopyresampled(
                $thumb,$src,
                0,0,0,0,
                $newW,$newH,
                $width,$height
            );
            imagejpeg($thumb,$uploadPath.$smallName,90);

            $previewImg = $smallName;
        }
    }

    if(!$error){

        if($edit){

            if($hasImage){
                mysqli_query($conn,"
                    UPDATE gallery SET
                        image_name='$image_name',
                        status='$status',
                        gallery_small_img='$smallName',
                        gallery_big_img='$bigName'
                    WHERE id=$id
                ");
            } else {
                mysqli_query($conn,"
                    UPDATE gallery SET
                        image_name='$image_name',
                        status='$status'
                    WHERE id=$id
                ");
            }

        } else {

            if(!$hasImage){
                $error = "Image is required";
            } else {
                mysqli_query($conn,"
                    INSERT INTO gallery
                    (image_name,status,gallery_small_img,gallery_big_img)
                    VALUES
                    ('$image_name','$status','$smallName','$bigName')
                ");
            }
        }

        if(!$error){
            header("Location: gallery_list.php");
            exit;
        }
    }
}
?>
