<?php
include_once '../header.php';
show_user_part();
show_menu_user("products.php");

global $global_url;
global $global_txt;
global $global_database;


$pid        = $_GET['pid'];             // id proizvoda
$user_id    = $_SESSION['user']['id'];  // id korisnika

$rating     = 5;
$comment    = "";
//- Ako je postan komentar provjeri je li sve ispravno
if(isset($_POST['submit']))
{
   $rating = $_POST['rating'];
   $comment= $_POST['comment'];
   
   if(empty($comment))
   {
        echo $error = $global_txt['comment_empty'];
   }
   else
   {
        $timestamp = date("Y-m-d H:i:s");
        $stmt = $global_database->prepare("INSERT INTO `comments` (`FK_comment_user_id`, `FK_comment_product_id`, `comment`, `comment_rating`, `comment_timestamp`) VALUES(?,?,?,?,?)");
        $stmt->bind_param('iisis', $user_id, $pid, $comment, $rating, $timestamp);
        $stmt->execute();
        $stmt->close();
        header('Location: ' . $global_url."views/products_details.php?pid=".$pid);
        return;
   }
}

$ratings = get_ratings($rating);

echo $global_txt['comment_add']."<br/>";
echo "<form actions='' method='POST'>";
    show_combobox($global_txt['comment_rating'], 'rating', $ratings);
    show_input_textarea_part($global_txt['comment_txt'], 'comment', 'comment', $comment);
    echo "<input type='submit' name='submit' value='".$global_txt['add']."'></input>";
echo "<form>";


//- popunjava polje za ratinge
function get_ratings($rating)
{
    $ratings = array();
    $r          = array();
    $r['id']    = "1";
    $r['name']  = "1";
    $r['selected'] = "";
    if($rating == $r['id'])
    {  
        $r['selected'] = "selected";
    }
    array_push($ratings, $r);
    
    $r['id']    = "2";
    $r['name']  = "2";
    $r['selected'] = "";
    if($rating == $r['id'])
    {  
        $r['selected'] = "selected";
    }
    array_push($ratings, $r);
    
    $r['id']    = "3";
    $r['name']  = "3";
    $r['selected'] = "";
    if($rating == $r['id'])
    {  
        $r['selected'] = "selected";
    }
    array_push($ratings, $r);
    
    $r['id']    = "4";
    $r['name']  = "4";
    $r['selected'] = "";
    if($rating == $r['id'])
    {  
        $r['selected'] = "selected";
    }
    array_push($ratings, $r);
    
    $r['id']    = "5";
    $r['name']  = "5";
    $r['selected'] = "";
    if($rating == $r['id'])
    {  
        $r['selected'] = "selected";
    }
    array_push($ratings, $r);
    
    return $ratings;
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
function show_input_textarea_part($label, $id, $name, $value)
{
    echo "<div class='reg_part'>";
        echo "<div class='reg_left'>";
            echo $label;
        echo "</div>";
        echo "<div class='reg_right'>";            
            echo "<textarea id='".$id."' name='".$name."' maxlength='1000' cols='50' rows='10'>".$value."</textarea>";
        echo "</div>";
    echo "</div>";
}

include_once '../footer.php';
/*comment.php*/