<?php 
include_once "../header.php";
show_user_part();
show_menu_user("homepage.php");

global $global_url;
global $global_txt;
global $global_database;

echo "<div id='homepage'>";
    echo $global_txt['homepage'];
    
    //- Prikazujemo izdvojene proizvode
    echo "<div class='hot_product'>";
    $hot  = "1";
    $stmt = $global_database-> prepare("SELECT product_id, product_name, product_price, product_img, product_quantity FROM products WHERE product_hot=?");
    $stmt->bind_param('i', $hot);
    $stmt->execute();
    $stmt->bind_result($id, $name, $price, $img, $quantity);
    while($stmt->fetch() != NULL)
    {
        show_hot_product($id, $name, $price, $img, $quantity);
    }
    $stmt->close();
    echo "</div>";
echo "</div>";

function show_hot_product($id, $name, $price, $img, $quantity)
{
    global $global_url;
    global $global_txt;
    echo "<div class='product_item'>";
        if(!empty($img))
        {
            echo "<img class='product_item_part' src='".$global_url."/img/".$img."'></img>";
        }
        else
        {
            echo "<img class='product_item_part' src='".$global_url."/img/nopic.jpg'></img>";
        }
        echo "<a href='".$global_url."/views/products_details.php?pid=".$id."'>".$global_txt['details']."</a>";
        echo "<span class='product_item_part'>".$name."</span>";
        echo "<span class='product_item_part'>".$price." kn</span>";
        if((isset($_SESSION['user']))&&($quantity > 0))
        {
            echo "<a class='product_item_part' href='".$global_url."/views/cart_add.php?id=".$id."'>".$global_txt['add_to_cart']."</a>";
        }
        else
        {
            if($quantity <= 0)
            {
                echo "<span class='sold_out'>".$global_txt['product_sold_out']."</span>";
            }  
        }
    echo "</div>";
}

include_once "../footer.php";
/* End of file homepage.php */

