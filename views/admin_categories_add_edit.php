<?php
include_once "../header.php";
show_user_part();
show_menu_admin("admin_categories.php");

global $global_url;
global $global_txt;
global $global_database;

$error_msg          = "";
$form_action_path   = $global_url."views/admin_categories_add_edit.php";  //- putanja na koju se posta forma
$name               = ""; //- naziv kategorije
$btn_txt            = $global_txt['add']; // tekst buttona - inicjalno postavljen na ADD, ako je edit ekran mijenjamo na edit

//- Provjeravamo radi li se o editu ili dodavanju 
$id = FALSE;
if(isset($_GET['id']))
{
    $id         = $_GET['id'];
    $btn_txt    = $global_txt['edit'];
    $form_action_path .= "?id=".$id;
}

// Provjera je li cancel pritisnut
if(isset($_POST['cancel']))
{
    header('Location: ' . $global_url."views/admin_categories.php");
    return;
}

//- Provjerava je li submitana forma
if(isset($_POST['submit']))
{
    //- Provjeri da li su upisani svi obavezni podaci
    if(empty($_POST['name']))
        $error_msg = $global_txt['empty_error'];
    else 
    {
        //- Ako je sve uredu onda napravi EDIT ili DODAJ
        if($id === FALSE) //- radi se o dodavanju 
        {
            $stmt = $global_database->prepare("INSERT INTO categories(category_name) VALUES(?)");
            $stmt->bind_param('s', $_POST['name']);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $global_url."views/admin_categories.php");
            return;
        }
        else //- radi se o editiranju
        {
            $stmt = $global_database->prepare("UPDATE categories SET category_name=? WHERE category_id=?");
            $stmt->bind_param('si', $_POST['name'], $id);
            $stmt->execute();
            $stmt->close();
            header('Location: ' . $global_url."views/admin_categories.php");
            return;
        }
    }
}

//- Ako je obični edit, potrebno je iz baze izvuči podatke koji se trebaju prikazivati
if($id != FALSE)
{
    $stmt = $global_database->prepare("SELECT category_name FROM categories WHERE category_id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($name);
    $stmt->fetch();
    $stmt->close();
}


//- Prikaz forme za edit/nova
echo "<form id='login' action='".$form_action_path."' method='POST'>";
    echo "<div class='error_msg'>";
        echo $error_msg;
    echo "</div>";
    
    echo $global_txt['tbl_categ_name'];
    echo "<input type='text' name='name' value='".$name."'></input>";
    
    echo "<input type='submit' name='submit' value='".$btn_txt."'></input>";
    echo "<input type='submit' name='cancel' value='".$global_txt['cancel']."'></input>";
echo "</form>";

include_once "../footer.php";
/* admin_categories_add_edit.php */