<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_users.php");

global $global_url;
global $global_txt;
global $global_database;

$stmt = $global_database->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param('s', $_GET['id']);
if($stmt->execute() > 0)
    echo "<a href='".$global_url."views/admin_users.php'>".$global_txt['delete_user_success']."</a>";
else
    echo "<a href='".$global_url."views/admin_users.php'>".$global_txt['delete_user_error']."</a>";
$stmt->close();

include_once "../footer.php";
/* admin_users_delete.php */