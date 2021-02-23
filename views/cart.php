<?php
include_once '../header.php';
show_user_part();
show_menu_user("cart.php");
global $global_url;
global $global_txt;
global $global_database;

///////////////////////////////////////////////////////////////////////////////////////////////
// UKLANJANJE PROIZVODA
//- ako je korisnik stisnuo gumb za uklanjanje proizvoda iz košarice, ukloni ga!
if(isset($_POST['remove_btn']))
{
    $delete_id = $_POST['remove_id'];
    $stmt = $global_database->prepare("DELETE FROM cart_user WHERE cu_id=?");
    $stmt->bind_param('i', $delete_id);
    $stmt->execute();
    $stmt->close();
    header('Location: ' . $global_url."views/cart.php"); // refreshamo stranicu da se sve updejta kak spada
    return;
}
///////////////////////////////////////////////////////////////////////////////////////////////
// SMANJIVANJE/POVEĆAVANJE KOLIČINE
if((isset($_POST['decrease_btn']))||(isset($_POST['increase_btn'])))
{
    $update_id          = $_POST['cu_id'];
    $update_quantity    = $_POST['cu_quantity'];
    if(isset($_POST['decrease_btn']))
        $update_quantity = $update_quantity - 1;
    else
        $update_quantity = $update_quantity + 1;
    
    $stmt = $global_database->prepare("UPDATE cart_user SET cu_product_quantity=? WHERE cu_id=?");
    $stmt->bind_param('ii', $update_quantity, $update_id);
    $stmt->execute();
    $stmt->close();
}

$cart_id  = 0;  // id košarice
$total    = 0;  // ukupna cijena
$user_id  = $_SESSION['user']['id'];
$products = get_cart_products($user_id, $total, $cart_id);
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
        echo "<th>".$global_txt['cart_action']."</th>";
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
echo "<a href='".$global_url."views/cart_buy.php?cid=".$cart_id."'>".$global_txt['buy']."</a>";
  


//- vraća proizvode iz košarice i preko reference ukupnu cijenu i id košarice
function get_cart_products($user_id, &$total, &$cart_id)
{
    global $global_database;
    $products = array();
    $stmt = $global_database->prepare("SELECT cu_id, FK_cu_cart_id, FK_cu_product_id, cu_product_quantity, product_name, product_price, product_quantity  FROM cart JOIN cart_user ON cart_user.FK_cu_cart_id = cart.cart_id  JOIN products ON products.product_id = cart_user.FK_cu_product_id WHERE  FK_cart_user_id=? AND cart_is_done = 0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($cu_id, $cart_id, $product_id, $quantity, $name, $price, $quantity_on_stock);
    while($stmt->fetch())
    {
        $p = array();
        $p['cu_id']         = $cu_id;
        $p['product_id']    = $product_id;
        $p['quantity']      = $quantity;
        $p['name']          = $name;
        $p['price']         = $price;
        $p['total']         = $price*$quantity;
        $p['quantity_on_stock'] = $quantity_on_stock;
        $total              += $p['total'];
        array_push($products, $p);
    }
    $stmt->close();
    return $products;
}

// Prikazuje proizvod u košarici
function show_product($p)
{
    global $global_url;
    global $global_txt;
    echo "<tr>";
        echo "<td>".$p['name']."</td>";
        echo "<td>".$p['price']." kn</td>";
        
        // količina
        echo "<td>";
            // za smanjivanje količine, samo ako je veća od 1
            if($p['quantity'] > 1)
            {
                echo "<form class='tbl_inline' action='".$global_url."views/cart.php' method='POST'>";
                    echo "<input type='text' class='hide_me' name='cu_id' value='".$p['cu_id']."'></input>";
                    echo "<input type='text' class='hide_me' name='cu_quantity' value='".$p['quantity']."'></input>";
                    echo "<input type='submit' class='tbl_btn' name='decrease_btn' value='-'></input>";
                echo "</form>";
            }
            else
                echo "<div class='tbl_inline tbl_btn'></div>";
            
            echo "<div class='tbl_inline'>".$p['quantity']."</div>";
            //- povećanje količine, samo ako ima na stanju!
            if(($p['quantity']+1) <= $p['quantity_on_stock'])
            {
                echo "<form class='tbl_inline' action='".$global_url."views/cart.php' method='POST'>";
                    echo "<input type='text' class='hide_me' name='cu_id' value='".$p['cu_id']."'></input>";
                    echo "<input type='text' class='hide_me' name='cu_quantity' value='".$p['quantity']."'></input>";
                    echo "<input type='submit' class='tbl_btn' name='increase_btn' value='+'></input>";
                echo "</form>";
            }
        echo "</td>";
        
        
        echo "<td>".$p['total']." kn</td>";
        echo "<td>";
                echo "<form action='".$global_url."views/cart.php' method='POST'>";
                    echo "<input type='text' class='hide_me' name='remove_id' value='".$p['cu_id']."'></input>";
                    echo "<input type='submit' class='tbl_click' name='remove_btn' value='".$global_txt['cart_remove']."'></input>";
                echo "</form>";
        echo "</td>";
    echo "</tr>";
}
include_once '../footer.php';
/*cart.php*/