<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_categories.php");

global $global_url;
global $global_txt;
global $global_database;

//- STRANIČENJE
// Prvo uzimamo broj kategorija za pagination
$stmt = $global_database->prepare("SELECT COUNT(*) AS num_of_rec FROM categories");
$stmt->execute();
$stmt->bind_result($num_of_rec);
$stmt->fetch();
$stmt->close();
if($num_of_rec <= 0)
{
    echo $global_txt['no_categories_in_db'];
    show_add_button();
    return;
}

/* Računamo straničenje*/
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

$sql    = "SELECT category_id, category_name FROM categories ORDER BY category_id ASC LIMIT ".$start.",".$end;
$result = $global_database->query($sql);

// PRIKAZ TABLICE
echo "<table>";
    echo "<thead>";
        echo "<th>".$global_txt['tbl_id']."</th>";
        echo "<th>".$global_txt['tbl_categ_name']."</th>";
        echo "<th>".$global_txt['tbl_edit']."</th>";
        echo "<th>".$global_txt['tbl_delete']."</th>";
    echo "</thead>";
    echo "<tbody>";
    foreach($result AS $res)
    {
        show_row($res['category_id'], $res['category_name']);
    }
    echo "</tbody>";
echo "</table>";
show_pagination($global_url."views/admin_categories.php", $per_page, $cur_page, $number_of_pages, $num_of_rec);
show_add_button();


// Kreira HTML za prikaz reda
function show_row($id, $name)
{
    global $global_url;
    global $global_txt;
    echo "<tr>";
        echo "<td>".$id."</td>";
        echo "<td>".$name."</td>";
        echo "<td><a href='".$global_url."views/admin_categories_add_edit.php?id=".$id."'>".$global_txt['tbl_edit']."</a></td>";
        echo "<td><a href='".$global_url."views/admin_categories_delete.php?id=".$id."'>".$global_txt['tbl_delete']."</a></td>";
    echo "</tr>";
}

// Kreira HTML za dodavanje kategorija
function show_add_button()
{
    global $global_url;
    global $global_txt;
    echo "<a href='".$global_url."views/admin_categories_add_edit.php'>";
        echo $global_txt['categories_add'];
    echo "</a>";
}
include_once "../footer.php";
/* admin_categories.php */