<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_products.php");

global $global_url;
global $global_txt;
global $global_database;

$stmt = $global_database->prepare("DELETE FROM products WHERE product_id=?");
$stmt->bind_param('i', $_GET['id']);
if($stmt->execute() > 0)
    echo "<a href='".$global_url."views/admin_products.php'>".$global_txt['delete_products_success']."</a>";
else
    echo "<a href='".$global_url."views/admin_products.php'>".$global_txt['delete_products_error']."</a>";
$stmt->close();
include_once "../footer.php";
/* admin_products_delete.php */