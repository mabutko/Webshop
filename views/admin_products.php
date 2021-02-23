<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_products.php");

global $global_url;
global $global_txt;
global $global_database;


$sql_where  = ""; //- ako smo odabrali točno određenu kategoriju onda 
$category_id = "0";
if(isset($_GET['cid']))
    $category_id = $_GET['cid'];

/* Sa lijeve strane su prikazane kategorije */
$categories = get_categories(); // uzimamo kateogije iz baze

// Ako nema kategorija upozori korisnika da mora prvo dodati kategoriju kako bi vidio proizvode
if(count($categories) <= 1)
{
    echo $global_txt['products_no_categories'];
    return;
}

echo "<div id='left'>";
    //- Prikaz kategorija
    foreach($categories AS $c)
    {
        show_category($c, $category_id);
    }
echo "</div>";

/* Sa desne strane su proizvodi kategorije */
//- STRANIČENJE
// Prvo uzimamo broj za pagination
$stmt = NULL;
$show_category = TRUE; // prikazujemo kategoriju samo ako je SVE odabrano
if($category_id > 0)
{
    $stmt = $global_database->prepare("SELECT COUNT(*) AS num_of_rec FROM products WHERE FK_product_category_id=?");
    $stmt->bind_param('i', $category_id);
    $show_category = FALSE;    
}
else // ako su svi proizvodi onda ne filtriraj po kategoriji
{
    $stmt = $global_database->prepare("SELECT COUNT(*) AS num_of_rec FROM products");
}
$stmt->execute();
$stmt->bind_result($num_of_rec);
$stmt->fetch();
$stmt->close();
if($num_of_rec <= 0)
{
    echo $global_txt['no_products_in_db'];
    show_add_button($category_id);
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

$sql    = "SELECT * FROM products JOIN categories ON categories.category_id = products.FK_product_category_id ORDER BY product_id ASC LIMIT ".$start.",".$end;
if($category_id > 0) // ako treba filtirati po kategoriji
{
    $sql    = "SELECT * FROM products JOIN categories ON categories.category_id = products.FK_product_category_id WHERE FK_product_category_id=".$category_id." ORDER BY product_id ASC LIMIT ".$start.",".$end;
}
$result = $global_database->query($sql);

echo "<div id='right'>";
    echo "<table>";
        echo "<thead>";
            echo "<th>".$global_txt['tbl_admin_products_id']."</th>";
            if($show_category === TRUE)
            {
                echo "<th>".$global_txt['tbl_admin_products_category']."</th>";
            }
            echo "<th>".$global_txt['tbl_admin_products_name']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_price']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_image']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_desc']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_size']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_color']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_quantity']."</th>";
            echo "<th>".$global_txt['tbl_admin_products_hot']."</th>";
            echo "<th>".$global_txt['tbl_edit']."</th>";
            echo "<th>".$global_txt['tbl_delete']."</th>";
        echo "</thead>";
        echo "<tbody>";
            foreach($result AS $res)
            {
                show_row($category_id, $res['product_id'], $res['category_name'], $res['product_name'], $res['product_price'], $res['product_img'], $res['product_desc'], $res['product_size'], $res['product_color'], $res['product_quantity'], $res['product_hot'], $show_category);
            }
        echo "</tbody>";
    echo "</table>";
    show_pagination($global_url."views/admin_products.php", $per_page, $cur_page, $number_of_pages, $num_of_rec);
    show_add_button($category_id);
echo "</div>";


//- vraća kategorije iz baze
function get_categories()
{
    global $global_database;
    global $global_txt;
    $categories = array();
    //- Dodajemo za sve kategorije na početku
    $c          = array();
    $c['id']    = "0";
    $c['name']  = $global_txt['all'];
    array_push($categories, $c);
    
    $stmt = $global_database->prepare("SELECT category_id, category_name FROM categories");
    $stmt->execute();
    $stmt->bind_result($category_id, $category_name);
    while($stmt->fetch())
    {
        $c          = array();
        $c['id']    = $category_id;
        $c['name']  = $category_name;
        array_push($categories, $c);
    }
    $stmt->close();
    return $categories;
}


//- Prikazuje kategorije
function show_category($c, $category_id)
{
    global $global_url;
    $cls = "";
    if($category_id == $c['id'])
        $cls = "sel";
    echo "<div class='submenu_item ".$cls."'>";
        echo "<a href='".$global_url."views/admin_products.php?cid=".$c['id']."'>";
            echo $c['name'];
        echo "</a>";
    echo "</div>";
}

// Kreira HTML za prikaz reda
function show_row($cid, $id, $category, $name, $price, $img, $desc, $size, $color, $quantity, $hot, $show_category)
{
    global $global_url;
    global $global_txt;
    echo "<tr>";
        echo "<td>".$id."</td>";
        if($show_category === TRUE)
        {
            echo "<td>".$category."</td>";
        }
        echo "<td>".$name."</td>";
        echo "<td>".$price."</td>";
        echo "<td>".$img."</td>";
        echo "<td>".$desc."</td>";
        echo "<td>".$size."</td>";
        echo "<td>".$color."</td>";
        echo "<td>".$quantity."</td>";
        echo "<td>".$hot."</td>";
        echo "<td><a href='".$global_url."views/admin_products_add_edit.php?cid=".$cid."&id=".$id."'>".$global_txt['tbl_edit']."</a></td>";
        echo "<td><a href='".$global_url."views/admin_products_delete.php?id=".$id."'>".$global_txt['tbl_delete']."</a></td>";
    echo "</tr>";
}

//- Button za dodavanje kategorije
function show_add_button($category_id)
{
    global $global_url;
    global $global_txt;
    echo "<a href='".$global_url."views/admin_products_add_edit.php?cid=".$category_id."'>";
        echo $global_txt['products_add'];
    echo "</a>";
}

include_once "../footer.php";
/* admin_products.php */