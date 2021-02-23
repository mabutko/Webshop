<?php
include_once '../header.php';
show_user_part();
show_menu_user("history.php");
global $global_url;
global $global_txt;
global $global_database;

$cart_id  = $_GET['cid'];  // id košarice
$total    = 0;  // ukupna cijena
$user_id  = $_SESSION['user']['id'];
$products = get_cart_products($cart_id, $user_id, $total);
if(count($products) <= 0)
{
    echo $global_txt['cart_empty'];
    return;
}
echo "<table>";
    echo "<thead>";
        echo "<th>".$global_txt['cart_name']."</th>";
        echo "<th>".$global_txt['cart_price']."</th>";
        echo "<th>".$global_txt['cart_quantity']."</th>";
        echo "<th>".$global_txt['cart_total']."</th>";
    echo "</thead>";
    echo "<tbody>";
    foreach($products AS $p)
    {
        show_product($p);
    }
    echo "</tbody>";
echo "</table>";
echo $global_txt['total_price'].":".$total." kn";
echo "<br/>";
echo "<a href='".$global_url."views/history.php'>".$global_txt['history_back']."</a>";
//- vraća proizvode iz košarice 
function get_cart_products($cart_id, $user_id, &$total)
{
    global $global_database;
    $products = array();
    $stmt = $global_database->prepare("SELECT cu_id, FK_cu_product_id, cu_product_quantity, product_name, product_price  FROM cart JOIN cart_user ON cart_user.FK_cu_cart_id = cart.cart_id  JOIN products ON products.product_id = cart_user.FK_cu_product_id WHERE  FK_cu_cart_id=? AND FK_cart_user_id=?");
    $stmt->bind_param('ii', $cart_id, $user_id);
    $stmt->execute();
    $stmt->bind_result($cu_id, $product_id, $quantity, $name, $price);
    while($stmt->fetch())
    {
        $p = array();
        $p['cu_id']         = $cu_id;
        $p['product_id']    = $product_id;
        $p['quantity']      = $quantity;
        $p['name']          = $name;
        $p['price']         = $price;
        $p['total']         = $price*$quantity;
        
        $total              += $p['total'];
        array_push($products, $p);
    }
    $stmt->close();
    return $products;
}

// Prikazuje proizvod u košarici
function show_product($p)
{
    echo "<tr>";
        echo "<td>".$p['name']."</td>";
        echo "<td>".$p['price']." kn</td>";
        echo "<td>".$p['quantity']."</td>";
        echo "<td>".$p['total']." kn</td>";
    echo "</tr>";
}
/* history_products.php */