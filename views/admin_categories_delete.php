<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_categories.php");

global $global_url;
global $global_txt;
global $global_database;

$stmt = $global_database->prepare("DELETE FROM categories WHERE category_id=?");
$stmt->bind_param('i', $_GET['id']);
if($stmt->execute() > 0)
    echo "<a href='".$global_url."views/admin_categories.php'>".$global_txt['delete_categories_success']."</a>";
else
    echo "<a href='".$global_url."views/admin_categories.php'>".$global_txt['delete_categories_error']."</a>";
$stmt->close();

include_once "../footer.php";
/* admin_categories_delete.php  */