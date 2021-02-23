<?php 
include_once "../header.php";
show_user_part();
show_menu_admin("admin_users.php");

global $global_url;
global $global_txt;
global $global_database;
    
//- Uzmi iz bate sve korisnike(osim admina) i omogući njihovo brisanje
$admin_name = "admin";
$stmt = $global_database->prepare("SELECT COUNT(*) AS num_of_rec FROM users WHERE username!=?");
$stmt->bind_param("s", $admin_name);
$stmt->execute();
$stmt->bind_result($num_of_rec);
$stmt->fetch();
$stmt->close();
if($num_of_rec <= 0)
{
    echo $global_txt['no_users_in_db'];
    return;
}

/* Računamo straničenje*/
//- STRANIČENJE
$cur_page   = 1;
$per_page   = 10;
if(isset($_GET['p']))
    $cur_page   = $_GET['p'];
if(isset($_GET['pp']))
    $per_page   = $_GET['pp'];

$number_of_pages    = "1";
$start              = "0";
$end                = $per_page;
pagination($per_page, $num_of_rec, $cur_page, $number_of_pages, $start, $end);

$sql    = "SELECT id, username, name, lastname, email, confirmed FROM users WHERE username!='admin' ORDER BY id ASC LIMIT ".$start.",".$end;
$result = $global_database->query($sql);

// PRIKAZ TABLICE
echo "<table>";
    echo "<thead>";
        echo "<th>".$global_txt['tbl_id']."</th>";
        echo "<th>".$global_txt['tbl_username']."</th>";
        echo "<th>".$global_txt['tbl_name']."</th>";
        echo "<th>".$global_txt['tbl_lastname']."</th>";
        echo "<th>".$global_txt['tbl_email']."</th>";
        echo "<th>".$global_txt['tbl_activate_user']."</th>";
        echo "<th>".$global_txt['tbl_delete']."</th>";
    echo "</thead>";
    echo "<tbody>";
    foreach($result AS $res)
    {
        show_row($res['id'], $res['username'], $res['name'], $res['lastname'], $res['email'], $res['confirmed']);
    }
    echo "</tbody>";
echo "</table>";
show_pagination($global_url."views/admin_users.php", $per_page, $cur_page, $number_of_pages, $num_of_rec);

// Kreira HTML za prikaz reda
function show_row($id, $username, $name, $lastname, $email, $confirmed)
{
    global $global_url;
    global $global_txt;
    echo "<tr>";
        echo "<td>".$id."</td>";
        echo "<td>".$username."</td>";
        echo "<td>".$name."</td>";
        echo "<td>".$lastname."</td>";
        echo "<td>".$email."</td>";
        if($confirmed > 0) // ako je aktiviran ponudi deaktivaciju
            echo "<td><a href='".$global_url."views/admin_users_activate.php?id=".$id."&c=0'>".$global_txt['tbl_deactivate']."</a></td>";
        else // inače ponudi aktivaciju korisnika
            echo "<td><a href='".$global_url."views/admin_users_activate.php?id=".$id."&c=1'>".$global_txt['tbl_activate']."</a></td>";
        echo "<td><a href='".$global_url."views/admin_users_delete.php?id=".$id."'>".$global_txt['tbl_delete']."</a></td>";
    echo "</tr>";
}
include_once "../footer.php";
/* admin_users.php */