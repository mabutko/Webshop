<?php
include_once '../header.php';
show_user_part();
show_menu_user("products.php");

global $global_url;
global $global_txt;
global $global_database;


$form_action_path = $global_url."views/products.php"; // za filter formu
$sql_where  = ""; //- slažemo where statement ovisno o filterima
$category_id = "0";
if(isset($_GET['cid']))
{
    $category_id = $_GET['cid'];
    $form_action_path .= "?cid=".$category_id;//- dodajemo u filter forme id kategorije
    if($category_id > 0)
        $sql_where = "WHERE FK_product_category_id='".$category_id."'";
}

/* Sa lijeve strane su prikazane kategorije */
$categories = get_categories(); // uzimamo kateogije iz baze

///////////////////////////////////////////////////////////////
// KATEGORIJE
// Ako nema kategorija upozori korisnika da mora prvo dodati kategoriju kako bi vidio proizvode
if(count($categories) <= 1)
{
    echo $global_txt['products_no_categories'];
    return;
}

///////////////////////////////////////////////////////////////
// FILTER
$order          = "";   //- order by 
$filter_color   = "0";  //- index odabrane boja
$filter_size    = "0";  //- index odabrane veličine
$color          = "";   //- naziv odabrane boja
$size           = "";   //- naziv odabrane veličine
/* Filter je pritisnut */
if(isset($_POST['submit']))
{
    $order          = $_POST['order'];
    $filter_color   = $_POST['colors'];
    $filter_size    = $_POST['sizes'];
}
else //- uvijek imamo neki order, defaultni je po nazivu ASC
{
    $order          = "product_name ASC";
    //- provjeravamo imamo li u $_GET neki filter jer kad koristimo pagination onda šaljemo u GET-u
    if(isset($_GET['o']))
        $order          = $_GET['o'];
    if(isset($_GET['c']))
        $filter_color   = $_GET['c'];
    if(isset($_GET['s']))
        $filter_size    = $_GET['s'];
}

$order_array = get_order_by($order);            //- uzimamo za order by
$sizes  = get_sizes($filter_size, $size);       //- uzimamo veličine za filter
$colors = get_colors($filter_color, $color);    //- uzimamo boje za filter

if($filter_color > 0)
{
    //- ako već imamo nešto u wheru dodajemo AND
    if(!empty($sql_where))
        $sql_where .= " AND ";
    else 
        $sql_where .= " WHERE ";
    $sql_where .= "product_color='".$color."'";
} 

if($filter_size > 0)
{
    //- ako već imamo nešto u wheru dodajemo AND
    if(!empty($sql_where))
        $sql_where .= " AND ";
    else 
        $sql_where .= " WHERE ";
    $sql_where .= "product_size='".$size."'";
} 

// ISPIS KATEGORIJA
echo "<div id='left'>";
    //- Prikaz kategorija
    foreach($categories AS $c)
    {
        show_category($c, $category_id, $order, $filter_color, $filter_size);
    }
echo "</div>";


///////////////////////////////////////////////////////////////

/* Sa desne strane su proizvodi kategorije */
//- STRANIČENJE
// Prvo uzimamo broj za pagination
$sql_count = "SELECT COUNT(*) AS num_of_rec FROM products ".$sql_where;
$stmt = $global_database->prepare($sql_count);
$stmt->execute();
$stmt->bind_result($num_of_rec);
$stmt->fetch();
$stmt->close();
if($num_of_rec <= 0)
{
    echo $global_txt['no_products_in_db'];
    return;
}
/* Računamo straničenje*/
$cur_page   = 1;
$per_page   = 8;
if(isset($_GET['p']))
    $cur_page   = $_GET['p'];
if(isset($_GET['pp']))
    $per_page   = $_GET['pp'];

$number_of_pages    = "1";
$start              = "0";
$end                = $per_page;
pagination($per_page, $num_of_rec, $cur_page, $number_of_pages, $start, $end);

$sql    = "SELECT * FROM products JOIN categories ON categories.category_id = products.FK_product_category_id ".$sql_where." ORDER BY ".$order." LIMIT ".$start.",".$end;
$result = $global_database->query($sql);

echo "<div id='right'>";
    show_filter($form_action_path, $sizes, $colors, $order_array);
    echo "<table>";
        foreach($result AS $res)
        {
            show_product($res);
        }
    echo "</table>";
    show_pagination($global_url."views/products.php", $per_page, $cur_page, $number_of_pages, $num_of_rec, "o=".$order, "c=".$filter_color,  "s=".$filter_size);
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
function show_category($c, $category_id, $order, $filter_color, $filter_size)
{
    global $global_url;
    $get = "?cid=".$c['id']."&o=".$order."&c=".$filter_color."&s=".$filter_size;
    $cls = "";
    if($category_id == $c['id'])
        $cls = "sel";
    echo "<div class='submenu_item ".$cls."'>";
        echo "<a href='".$global_url."views/products.php".$get."'>";
            echo $c['name'];
        echo "</a>";
    echo "</div>";
}

// Kreira HTML za prikaz reda
function show_product($res)
{
    global $global_url;
    global $global_txt;
    echo "<div class='product_item'>";
        if(!empty($res['product_img']))
        {
            echo "<img class='product_item_part' src='".$global_url."img/".$res['product_img']."'></img>";
        }
        else
        {
            echo "<img class='product_item_part' src='".$global_url."img/nopic.jpg'></img>";
        }
        echo "<a href='".$global_url."views/products_details.php?pid=".$res['product_id']."'>".$global_txt['details']."</a>";
        echo "<span class='product_item_part'>".$res['product_name']."</span>";
        echo "<span class='product_item_part'>".$res['product_price']." kn</span>";
        if((isset($_SESSION['user']))&&($res['product_quantity'] > 0))
        {
            echo "<a class='product_item_part' href='".$global_url."views/cart_add.php?id=".$res['product_id']."'>".$global_txt['add_to_cart']."</a>";
        }
        else
        {
            if($res['product_quantity'] <= 0)
            {
                echo "<span class='sold_out'>".$global_txt['product_sold_out']."</span>";
            }  
        }
    echo "</div>";
}

//- Puni polje sa vrijednostima za sortiranje:
function get_order_by($selected)
{
    global $global_txt;
    $values = array();
    $c          = array();
    $c['id']    = "product_name ASC";
    $c['name']  = $global_txt['order_by_name_asc'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    $c['id']    = "product_name DESC";
    $c['name']  = $global_txt['order_by_name_des'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    
    $c['id']    = "product_price ASC";
    $c['name']  = $global_txt['order_by_price_asc'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    $c['id']    = "product_price DESC";
    $c['name']  = $global_txt['order_by_price_des'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    
 
    return $values;
}

//- Vraća sve veličine iz baze
function get_sizes($selected, &$value)
{
    global $global_database;
    global $global_txt;
    $values = array();
    //- Dodajemo za sve veličine na početku
    $i          = 0;
    $c          = array();
    $c['id']    = $i++;
    $c['name']  = $global_txt['filter_sizes_all'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    $stmt = $global_database->prepare("SELECT DISTINCT product_size FROM products ORDER BY product_size ASC");
    $stmt->execute();
    $stmt->bind_result($product_size);
    while($stmt->fetch())
    {
        $c          = array();
        $c['id']    = $i++;
        $c['name']  = $product_size;
        $c['selected'] = "";
        if($selected == $c['id'])
        {
            $c['selected'] = "selected";
            $value         = $c['name'];
        }
        array_push($values, $c);
    }
    $stmt->close();
    return $values;
}
//- Vraća sve boje iz baze
function get_colors($selected, &$value)
{
    global $global_database;
    global $global_txt;
    $values = array();
    //- Dodajemo za sve veličine na početku
    $i          = 0;
    $c          = array();
    $c['id']    = $i++;
    $c['name']  = $global_txt['filter_colors_all'];
    $c['selected'] = "";
    if($selected == $c['id'])
        $c['selected'] = "selected";
    array_push($values, $c);
    
    $stmt = $global_database->prepare("SELECT DISTINCT product_color FROM products ORDER BY product_color ASC");
    $stmt->execute();
    $stmt->bind_result($product_color);
    while($stmt->fetch())
    {
        $c          = array();
        $c['id']    = $i++;
        $c['name']  = $product_color;
        $c['selected'] = "";
        if($selected == $c['id'])
        {
            $c['selected'] = "selected";
            $value         = $c['name'];
        }
        array_push($values, $c);
    }
    $stmt->close();
    return $values;
}

//- Prikazuje filter iznad proizvoda
function show_filter($form_action_path, $sizes, $colors, $order_array)
{
    global $global_txt;
    echo "<form id='filter_method' action='".$form_action_path."'  method='POST' >";
        show_combobox($global_txt['filter_order_by'], "order", $order_array);
        show_combobox($global_txt['filter_sizes'], "sizes", $sizes);
        show_combobox($global_txt['filter_colors'], "colors", $colors);
        echo "<input type='submit' name='submit' value='".$global_txt['filter_btn']."'></input>";
    echo "</form>";
}

// Iscrtava combobox
function show_combobox($label, $name, $items)
{
    echo $label;
    echo "<select name=".$name.">";
        foreach($items AS $item)
        {
            echo " <option value='".$item['id']."' ".$item['selected'].">".$item['name']."</option>";
        }
    echo "</select>";
}

include_once '../footer.php';
/* admin_products.php */
/* products.php */