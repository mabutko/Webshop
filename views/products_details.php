<?php
include_once '../header.php';
show_user_part();
show_menu_user("products.php");
global $global_url;
global $global_txt;
global $global_database;

$user_id = -1;
if(isset($_SESSION['user']))
{
    $user_id = $_SESSION['user']['id'];
}

$pid = $_GET['pid']; // id proizvoda
$stmt = $global_database->prepare("SELECT product_name, product_price, product_img, product_desc, product_size, product_color, product_quantity, category_name  "
                                    . " FROM products JOIN categories ON products.FK_product_category_id=categories.category_id WHERE product_id=?");
$stmt->bind_param('i', $pid);
$stmt->execute();
$stmt->bind_result($name, $price, $img, $desc, $size, $color, $quantity, $category);
$stmt->fetch();
$stmt->close();
// ako nema proizvoda ispiši poruku
if(empty($name))
{
    echo $global_txt['no_product'];
    return;
}
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// DETALJI PROIZVODA
echo "<div class='product_item'>";
    if(!empty($img))
    {
        echo "<img class='product_item_part' src='".$global_url."img/".$img."'></img>";
    }
    else
    {
        echo "<img class='product_item_part' src='".$global_url."img/nopic.jpg'></img>";
    }
    if((isset($_SESSION['user']))&&($quantity > 0))
    {
        echo "<a class='product_item_part' href='".$global_url."views/cart_add.php?id=".$pid."'>".$global_txt['add_to_cart']."</a>";
    }
    else
    {
        if($quantity <= 0)
        {
            echo "<span class='sold_out'>".$global_txt['product_sold_out']."</span>";
        }
    }
echo "</div>";
echo "<div class='product_item'>";
    echo "<span class='product_item_part'>".$global_txt['product_name']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_price']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_desc']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_size']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_color']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_quantity']."</span>";
    echo "<span class='product_item_part'>".$global_txt['product_category']."</span>";
echo "</div>";

echo "<div class='product_item'>";
    echo "<span class='product_item_part'>".$name."</span>";
    echo "<span class='product_item_part'>".$price." kn</span>";
    echo "<span class='product_item_part'>".$desc."</span>";
    echo "<span class='product_item_part'>".$size."</span>";
    echo "<span class='product_item_part'>".$color."</span>";
    echo "<span class='product_item_part'>".$quantity."</span>";
    echo "<span class='product_item_part'>".$category."</span>";
echo "</div>";

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// KOMENTARI
$average_rating     = 0;
$already_commented  = FALSE; // biti će true kada ako je korisnik već komentirao ovaj proizovd, tako onemogućavam ponovno komentiranje
$comments = get_comments($pid, $average_rating, $user_id, $already_commented);
echo "<div style='clear: both;'></div>"; // čistimo float css product_itema http://css-tricks.com/the-how-and-why-of-clearing-floats/
echo "<div class='comments'>";
echo "<br/>";
echo $global_txt['comments'];
echo "<br/>";
if(count($comments) <= 0)
{
    echo $global_txt['no_comments']."<br/>";
    show_add_comment($pid, $already_commented);
    return;
}
else
{
    foreach ($comments AS $c)
    {
        echo "<hr/>";
        echo "<div class='comment_h'>";
            echo "<div class='comment_h_part'>";
                echo $global_txt['comment_rating'].":".$c['rating'];
            echo "</div>";
            echo "<div class='comment_h_part'>";
                echo $c['timestamp']."|".$c['username'];;
            echo "</div>";
        echo "</div>";
        echo "<div class='comment_b'>";
            echo $c['comment'];
        echo "</div>";
    }
}
echo "<br/>";
show_add_comment($pid, $already_commented);
echo "</div>";

//- Vraća komentare iz baze
function get_comments($pid, &$average_rating, $user_id, &$already_commented)
{
    global $global_database;
    $comments = array();
    $stmt = $global_database->prepare("SELECT comment_id, comment, comment_rating, comment_timestamp, username, id"
                                    . " FROM comments JOIN users ON users.id=comments.FK_comment_user_id WHERE FK_comment_product_id=?");
    $stmt->bind_param('i', $pid);
    $stmt->execute();
    $stmt->bind_result($id, $comment, $rating, $timestamp, $username, $uid);
    $counter = 0;
    while($stmt->fetch())
    {
        $c                  = array();
        $c['id']            = $id;
        $c['comment']       = $comment;
        $c['rating']        = $rating;
        $c['timestamp']     = $timestamp;
        $c['username']      = $username;
        
        if($user_id == $uid)
        {
            $already_commented = TRUE;
        }
        array_push($comments, $c);
        $average_rating     += $rating;
        $counter++;
    }
    if($counter > 0)
        $average_rating = ($average_rating/$counter);
    
    $stmt->close();
    return $comments;
}

//- prikazuje link za dodavanje komentara
function show_add_comment($pid, $already_commented)
{
    global $global_url;
    global $global_txt;
    // samo ako je korisnik prijavljen, ostali ne mogu dodavati komentar
    if((isset($_SESSION['user']))&&($already_commented == FALSE)) 
    {
        echo "<a href='".$global_url."views/comment.php?pid=".$pid."'>".$global_txt['comment_add']."</a>";
    }
}

include_once '../footer.php';
/*products_details.php*/