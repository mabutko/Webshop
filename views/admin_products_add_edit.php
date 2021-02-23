<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_products.php");

global $global_url;
global $global_txt;
global $global_database;

$error_msg          = "";
$name               = ""; //- naziv 
$price              = ""; //- cijena
$desc               = ""; //- opis
$color              = ""; //- boja
$size               = ""; //- veličina
$hot                = "0"; //- 0 - ne prikazuj na naslovnici, 1 - prikazi
$quantity           = "100"; //- inicijalna vrijednost
$img                = ""; //- slika, putanja
$btn_txt            = $global_txt['add']; // tekst buttona - inicjalno postavljen na ADD, ako je edit ekran mijenjamo na edit
$form_action_path   = $global_url."views/admin_products_add_edit.php";
//- Provjeravamo radi li se o editu ili dodavanju 
$id = FALSE;
if(isset($_GET['id']))
{
    $id         = $_GET['id'];
    $btn_txt    = $global_txt['edit'];
    $form_action_path .= "?id=".$id;
}

//- Provjeravamo je li proslijeđen id kategorije
$cid = 0;
if(isset($_GET['cid']))
{
    $cid = $_GET['cid'];
}

// Provjera je li cancel pritisnut
if(isset($_POST['cancel']))
{
    header('Location: ' . $global_url."views/admin_products.php");
    return;
}
if(isset($_POST['hot']))
   $hot = 1;

/* Ako je poslana slika(nakon uploada)*/
$image = "";
if(isset($_GET['img']))
{
    $image = $_GET['img'];
}

//- Provjerava je li submitana forma
if(isset($_POST['submit']))
{
    //- Provjeri da li su upisani svi obavezni podaci
    if((empty($_POST['name']))||(empty($_POST['price'])))
        $error_msg = $global_txt['empty_error'];
    else 
    {
        //- Ako je sve uredu onda napravi EDIT ili DODAJ
        if($id === FALSE) //- radi se o dodavanju 
        {
            $stmt = $global_database->prepare("INSERT INTO products(FK_product_category_id, product_name, product_price, product_img, product_desc, product_size, product_color, product_hot, product_quantity) VALUES(?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('issssssii', $_POST['category'], $_POST['name'], $_POST['price'], $_POST['img'], $_POST['desc'], $_POST['size'], $_POST['color'], $hot, $_POST['quantity']);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $global_url."views/admin_products.php");
            return;
        }
        else //- radi se o editiranju
        {
            $stmt = $global_database->prepare("UPDATE products SET FK_product_category_id=?, product_name=?, product_price=?, product_img=?, product_desc=?, product_size=?, product_color=?, product_hot=?, product_quantity=? WHERE product_id=?");
            $stmt->bind_param('issssssiii', $_POST['category'], $_POST['name'], $_POST['price'], $_POST['img'], $_POST['desc'], $_POST['size'], $_POST['color'], $hot, $_POST['quantity'], $id);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $global_url."views/admin_products.php");
            return;
        }
    }
}

//- Ako je obični edit, potrebno je iz baze izvuči podatke koji se trebaju prikazivati
if($id != FALSE)
{
    $stmt = $global_database->prepare("SELECT product_name, product_price, product_img, product_desc, product_size, product_color, product_quantity, product_hot FROM products WHERE product_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($name, $price, $img, $desc, $size, $color, $quantity, $hot);
    $stmt->fetch();
    $stmt->close();
    
    if(!empty($image))
        $img = $image;
}

//- Potrebno je izvuči kategorije iz baze
$categories = get_categories($cid);

echo "<div class='error_msg'>";
    echo $error_msg;
echo "</div>";
    
echo "<form action='".$global_url."views/admin_products_upload_img.php"."?cid=".$cid."&id=".$id."' method='post' enctype='multipart/form-data'>";
    echo $global_txt['image_to_upload'];
    echo "<input type='file' name='fileToUpload' id='fileToUpload'>";
    echo "<input type='submit' value='".$global_txt['image_upload']."' name='submit'>";
echo "</form>";
echo "<hr/>";
//- Prikaz forme za edit/nova
echo "<form id='login' action='".$form_action_path."' method='POST'>";
    show_combobox($global_txt['tbl_admin_products_category'], "category", $categories);
    show_input($global_txt['tbl_admin_products_name'],     "name",     $name,      TRUE);
    show_input($global_txt['tbl_admin_products_price'],    "price",    $price,     TRUE);
    show_input($global_txt['tbl_admin_products_desc'],     "desc",     $desc,      FALSE);
    show_input($global_txt['tbl_admin_products_size'],     "size",     $size,      FALSE);
    show_input($global_txt['tbl_admin_products_color'],    "color",    $color,     FALSE);
    show_input($global_txt['tbl_admin_products_quantity'], "quantity", $quantity,  FALSE);
    show_input($global_txt['tbl_admin_products_image'],    "img",      $img,       FALSE);
    show_checkbox($global_txt['tbl_admin_products_hot'],   "hot",      $hot);

    echo "<input type='submit' name='submit' value='".$btn_txt."'></input>";
    echo "<input type='submit' name='cancel' value='".$global_txt['cancel']."'></input>";
echo "</form>";


    
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

// Iscrtava input
function show_input($label, $name, $value, $isMandatory)
{
    echo $label;
    echo "<input type='text' name='".$name."' value='".$value."'></input>";
    if($isMandatory === TRUE)
        echo "*";
}

// Iscrtava checkbox
function show_checkbox($label, $name, $value)
{
    $checked= "";
    if($value > 0)
        $checked= "checked";
    
    echo "<input type='checkbox' name='".$name."' value='".$value."' ".$checked.">".$label."</input>";
}

// Vraća kategorije iz baze
//- vraća kategorije iz baze
function get_categories($cid)
{
    global $global_database;
    global $global_txt;
    $categories = array();
    
    $stmt = $global_database->prepare("SELECT category_id, category_name FROM categories");
    $stmt->execute();
    $stmt->bind_result($category_id, $category_name);
    while($stmt->fetch())
    {
        $c              = array();
        $c['id']        = $category_id;
        $c['name']      = $category_name;
        $c['selected']  = "";
        if($cid == $category_id)
        {
            $c['selected'] = "selected";
        }
        
        array_push($categories, $c);
    }
    $stmt->close();
    return $categories;
}
include_once "../footer.php";
/* admin_products_add_edit.php */