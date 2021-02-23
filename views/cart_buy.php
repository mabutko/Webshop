<?php
include_once '../header.php';
global $global_url;
global $global_txt;
global $global_database;

$user_id = $_SESSION['user']['id'];
$cart_id = $_GET['cid'];
$timestamp = date("Y-m-d H:i:s");
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//- Provjera ima li dovoljno proizvoda na stanju!
$total = 0;
$products               = array(); //- tu će biti svi proizvodi i količine da ih možemo updejtati u bazi!
$insufficient_quantity  = array(); //- ovdje su samo vrijednosti ako imamo previše proizvoda naručenih
$stmt = $global_database->prepare("SELECT cu_id, FK_cu_cart_id, FK_cu_product_id, cu_product_quantity, product_name, product_price, product_quantity  FROM cart JOIN cart_user ON cart_user.FK_cu_cart_id = cart.cart_id  JOIN products ON products.product_id = cart_user.FK_cu_product_id WHERE  FK_cart_user_id=? AND cart_is_done = 0");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($cu_id, $cart_id, $product_id, $quantity, $name, $price, $quantity_on_stock);
while($stmt->fetch())
{
    $p                      = array();
    $p['id']                = $product_id;
    $p['name']              = $name;
    $p['quantity']          = $quantity;
    $p['quantity_on_stock'] = $quantity_on_stock;
    $p['new_quantity']      = $p['quantity_on_stock']-$p['quantity'];
    // ako smo dodali nekog proizvoda više nego što je dozovljeno dodaj ga na listu
    if($quantity > $quantity_on_stock)
    {
        array_push($insufficient_quantity, $p);
    }
    $total              += $price*$quantity;
    array_push($products, $p);
}
$stmt->close();

//- Ako smo stavili nekog porizvoda više nego je dozvoljeno, lista će imati proizvode pa ispiši pogreške!
if(count($insufficient_quantity) > 0)
{
    echo "<div class='qunatity_error'>";
        echo "<div class='qunatity_error_title'>".$global_txt['buy_cart_quantity_error']."</div>";
    foreach($insufficient_quantity AS $p)
    {
        echo "<div class='qunatity_error_val'>".$p['name']."&nbsp;".$global_txt['buy_cart_availabe'].$p['quantity_on_stock']."&nbsp;".$global_txt['buy_cart_ordered'].$p['quantity']."</div>";
    }
        echo "<div class='qunatity_error_ret'><a href='".$global_url."views/cart.php'>".$global_txt['buy_cart_error_return']."</a></div>";
    echo "</div>";
    include_once '../footer.php';
    return;
}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//- Provjeri ima li novaca korisnik na računu
$money = 0;
$stmt = $global_database->prepare("SELECT money FROM users WHERE id=?");
$stmt->bind_param('i', $user_id);
$stmt->bind_result($money);
$stmt->execute();
$stmt->fetch();
$stmt->close();
// Ako je ukupna cijena veća od raspoloživog stanja, nemoj izvesti kupnju!
if($total > $money)
{
    echo "<a href='".$global_url."views/cart.php'>".$global_txt['buy_cart_error_no_money']."</a>";
}
else // - ako je sve ok, izvedi kupnju
{
    $stmt = $global_database->prepare("UPDATE cart SET cart_is_done=1, cart_timestamp=? WHERE cart_id=? AND FK_cart_user_id=? ");
    $stmt->bind_param('sii', $timestamp, $cart_id, $user_id);
    if($stmt->execute() > 0)
    {
        $stmt->close();
        //- ako je kupnja uspiješna napravi i update novaca
        $money = $money - $total;
        $stmt = $global_database->prepare("UPDATE users SET money=? WHERE id=? ");
        $stmt->bind_param('di', $money, $user_id);
        $stmt->execute();
        $stmt->close();
        
        //- te smanji količinu proizvoda na stanju
        foreach($products AS $p)
        {
            $stmt = $global_database->prepare("UPDATE products SET product_quantity=? WHERE product_id=? ");
            $stmt->bind_param('ii', $p['new_quantity'], $p['id']);
            $stmt->execute();
            $stmt->close();
        }
        echo "<a href='".$global_url."views/cart.php'>".$global_txt['buy_cart_success']."</a>";
    }
    else
    {
        $stmt->close();
        echo "<a href='".$global_url."'>".$global_txt['buy_cart_error']."</a>";
    }
    
}
include_once '../footer.php';
/*cart_buy.php*/