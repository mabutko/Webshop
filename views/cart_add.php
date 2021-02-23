<?php
include_once '../header.php';
global $global_url;
global $global_txt;
global $global_database;

$user_id        = $_SESSION['user']['id'];
$product_id     = $_GET['id'];
$cart_id        = 0;
////////////////////////////////////////////////////////////////////////////////////////////////////////////
// KREIRANJE KOŠARICE(UZIMANJE POSTOJEĆE AKO POSTOJI)
//- Provjeri postoji li košarica koja nije plaćena za tog korisnika, ako postoji uzmi njen id, inače je kreiraj
$stmt = $global_database->prepare("SELECT cart_id FROM cart WHERE FK_cart_user_id=? AND cart_is_done=0");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($cart_id);
$stmt->fetch();
$stmt->close();
// Ako nema košarice kreirane za tog usera kreiraj je
if($cart_id <= 0)
{
    $stmt = $global_database->prepare("INSERT INTO cart(FK_cart_user_id) VALUES(?)");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
    //- uzmi id 
    $stmt = $global_database->prepare("SELECT cart_id FROM cart WHERE FK_cart_user_id=? AND cart_is_done=0");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($cart_id);
    $stmt->fetch();
    $stmt->close();
    
    if($cart_id <= 0)
    {
        echo "<a href='".$global_url."'>".$global_txt['add_to_cart_error']."</a>";
        return;
    }
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DODAVANJE PROIZVODA
//- Prvo provjeri postoji li u košarici
$stmt = $global_database->prepare("SELECT cu_id, cu_product_quantity FROM cart_user WHERE FK_cu_cart_id=? AND FK_cu_product_id=?");
$stmt->bind_param('ii', $cart_id, $product_id);
$stmt->execute();
$stmt->bind_result($cu_id, $quantity);
$stmt->fetch();
$stmt->close();

//- ako je proizvod već u košarici povećaj mu količinu za 1
if($cu_id > 0)
{
    $quantity = $quantity + 1;
    $stmt = $global_database->prepare("UPDATE cart_user SET cu_product_quantity=? WHERE cu_id=?");
    $stmt->bind_param('ii', $quantity, $cu_id);
    if($stmt->execute() > 0)
        echo "<a href='".$global_url."views/cart.php'>".$global_txt['add_to_cart_success']."</a>";
    else
        echo "<a href='".$global_url."'>".$global_txt['add_to_cart_error']."</a>";
    $stmt->close();
}
else // inače ga dodaj u košaricu
{
    $stmt = $global_database->prepare("INSERT INTO cart_user(FK_cu_cart_id, FK_cu_product_id) VALUES (?,?)");
    $stmt->bind_param('ii', $cart_id, $product_id);
    if($stmt->execute() > 0)
        echo "<a href='".$global_url."views/cart.php'>".$global_txt['add_to_cart_success']."</a>";
    else
        echo "<a href='".$global_url."'>".$global_txt['add_to_cart_error']."</a>";
    $stmt->close();
}
include_once '../footer.php';
/*cart_add.php*/