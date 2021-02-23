<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_users.php");

global $global_url;
global $global_txt;
global $global_database;

$stmt = $global_database->prepare("UPDATE users SET confirmed=? WHERE id=?");
$stmt->bind_param('ii', $_GET['c'], $_GET['id']);
if($stmt->execute() > 0)
{
    // Poruka 
    if($_GET['c'] > 0) // aktivacija uspiješna
        echo "<a href='".$global_url."views/admin_users.php'>".$global_txt['activate_user_success']."</a>";
    else // deaktivacija uspiješna
        echo "<a href='".$global_url."views/admin_users.php'>".$global_txt['deactivate_user_success']."</a>";
}
else
    echo "<a href='".$global_url."views/admin_users.php'>".$global_txt['activate_user_error']."</a>";
$stmt->close();

include_once "../footer.php";
/* admin_users_activate.php */