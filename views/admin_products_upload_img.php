<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_products.php");

global $global_url;
global $global_txt;

$id = FALSE;
if(isset($_GET['id']))
{
    $id         = $_GET['id'];
}

//- Provjeravamo je li proslijeđen id kategorije
$cid = 0;
if(isset($_GET['cid']))
{
    $cid = $_GET['cid'];
}

// UPLOAD FILEA
$target_file = "../img/". basename($_FILES["fileToUpload"]["name"]); // ../img jer je img folder u folderu na istoj razini kao views(u njemu smo trenutno jer je tamo admin_products_uload_img.php) kad bi pisalo samo /img on bi tražio taj folder u views folderu
$uploadOk = 1;
$imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
   
    $count = (int)count($_FILES["fileToUpload"]["tmp_name"]);
    if($count > 0)
    {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
            echo $global_txt['upload_file_is_img'] . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo $global_txt['upload_no_image'];
            $uploadOk = 0;
        }
    }
    else
    {
        echo $global_txt['upload_no_image'];
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo $global_txt['upload_file_exists'];
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 500000) {
    echo $global_txt['upload_large'];
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo $global_txt['upload_sup_files'];
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "<a href='".$global_url."views/admin_products_add_edit.php?cid=".$cid."&id=".$id."'>".$global_txt['upload_click']."</a>";
// if everything is ok, try to upload file
} else {
    
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "<a href='".$global_url."views/admin_products_add_edit.php?cid=".$cid."&id=".$id."&img=".$_FILES["fileToUpload"]["name"]."'>".$global_txt['upload_done1']. basename( $_FILES["fileToUpload"]["name"]). $global_txt['upload_done2'].$global_txt['upload_click']."</a>";
    } else {
        echo "<a href='".$global_url."views/admin_products_add_edit.php?cid=".$cid."&id=".$id."'>".$global_txt['upload_error'].$global_txt['upload_click']."</a>";
    }
}
include_once "../footer.php";
/*admin_products_upload_img.php*/