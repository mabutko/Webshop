<?php
include_once '../header.php';
show_user_part();
show_menu_user("history.php");
global $global_url;
global $global_txt;
global $global_database;

$total      = 0.0;
$money      = 0.0;
$money_to_add = 100.0;
$user_id    = $_SESSION['user']['id'];
$carts      = get_carts($user_id, $total);


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//- Uzimamo novac(stanje računa) iz baze
$stmt = $global_database->prepare("SELECT money FROM users WHERE id=? ");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->bind_result($money);
$stmt->fetch();
$stmt->close();
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//- Pregled sredstava na računu i dodavanje istog
// Provjera da li izvršavamo plaćanje
if(isset($_POST['money_add']))
{
    $money_to_add   = $_POST['money_to_add'];
    $money          = floatval($money) + floatval($money_to_add); // stanje na računu računamo kao trenutno plus doplačeno!
    $stmt = $global_database->prepare("UPDATE users SET money=? WHERE id=? ");
    $stmt->bind_param('di', $money, $user_id);
    $stmt->execute();
    $stmt->close();
}

$form_action_path = $global_url."views/history.php";
echo "<form class='h_money' action='".$form_action_path."' method='POST'>";
    echo "<span class='h_money_l'>".$global_txt['history_money']."</span>";
    echo "<span class='h_money_m'>".$money." kn</span>";
    echo "<span class='h_money_b'>".$global_txt['history_add_money']."</span>";
    echo "<input id='money_to_add' name='money_to_add' class='h_money_input' type='text' value='".$money_to_add."'></input>&nbsp;&nbsp;";
    echo "<input id='money_add' name='money_add' class='h_money_btn' type='submit' value='".$global_txt['history_add_money_btn']."'></input>";
echo "</form>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if(count($carts) <= 0)
{
    echo $global_txt['history_empty'];
    return;
}
echo "<table>";
    echo "<thead>";
        echo "<th>".$global_txt['history_date']."</th>";
        echo "<th>".$global_txt['history_quantity']."</th>";
        echo "<th>".$global_txt['history_price']."</th>";
    echo "</thead>";
    echo "<tbody>";
    foreach($carts AS $c)
    {
        show_cart_for_user($c);
    }
    echo "</tbody>";
echo "</table>";
echo $global_txt['history_total'].":".$total." kn";
echo "<br/>";


//- vraća kupnje korisnika
function get_carts($user_id, &$total)
{
    global $global_database;
    $carts = array();
    $stmt = $global_database->prepare("SELECT cart_id, cart_timestamp, cu_product_quantity, product_price FROM cart JOIN cart_user ON cart_user.FK_cu_cart_id=cart.cart_id JOIN products ON products.product_id=cart_user.FK_cu_product_id WHERE FK_cart_user_id = ? AND cart_is_done = 1 ORDER BY cart_id ASC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($cart_id, $timestamp, $quantity, $price);
    while($stmt->fetch())
    {
        $pos = -1;
        // Pronađi postoji li ta kupnja već u listi
        for($i = 0; $i < count($carts); $i++)
        {
            if($carts[$i]['id'] == $cart_id)
            {
                $pos = $i;
                break;
            }
        }
        
        //- ako ne postoji, kreiraj je i dodaj u listu
        if($pos === -1)
        {
            $c              = array();
            $c['id']        = $cart_id;
            $c['time']      = $timestamp;
            $c['quantity']  = 0;
            $c['total']     = 0;
            $pos            = count($carts); // uzimamo poziciju na koju je dodana
            array_push($carts, $c);
        }
        
        //- zbrajaj cijene da dođemo do totala
        $carts[$pos]['quantity'] += $quantity;
        $carts[$pos]['total']    += ($price*$quantity);
        $total                   += ($price*$quantity);
    }
    $stmt->close();
    return $carts;
}

function show_cart_for_user($c)
{
    global $global_url;
    global $global_txt;
    echo "<tr>";
        echo "<td>".$c['time']."</td>";
        echo "<td>".$c['quantity']."</td>";
        echo "<td>".$c['total']." kn</td>";
        echo "<td><a href='".$global_url."views/history_products.php?cid=".$c['id']."'>".$global_txt['history_details']."</a></td>";
    echo "</tr>";
}

include_once '../footer.php';
/* history.php */