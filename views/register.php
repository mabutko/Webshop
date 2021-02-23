<?php 
include_once '../header.php';
show_menu_user("register.php");

global $global_txt;
global $global_database;
global $global_url;

$error_msg  = "";   //- varijabla za poruku o pogrešci, inicijalno nema pogreške
$username   = "";
$name       = "";
$lastname   = "";
$email      = "";

//- Provjera da li je forma submitana, ako je onda provjeravamo podatke, inače smo prvi put otvorili stranicu pa prikazujemo praznu login formu
if(isset($_POST['submit']))
{
    $username   = $_POST['username'];
    $name       = $_POST['name'];
    $lastname   = $_POST['lastname'];
    $email      = $_POST['email'];
    $isOK       = TRUE;
    //- Provjerava se odgovarju li lozinka i ponovljena lozinka
    if($_POST['password'] != $_POST['repassword'])
    {
        $error_msg = $global_txt['mismatch_psw'];
        $isOK       = FALSE;
    }
    //- Provjerava se da li je email ispravnog formata - http://php.net/manual/en/function.filter-var.php
    if(filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL ) === FALSE)
    {
        $error_msg = $global_txt['email_error'];
        $isOK       = FALSE;
    }    
    //- username, password i email moraju biti upisani
    if((empty($_POST['username']))||(empty($_POST['password']))||(empty($_POST['repassword']))||(empty($_POST['email'])))
    {
        $error_msg  = $global_txt['empty_error'];
        $isOK       = FALSE;
    }
    
    //- Ako nema pogrešaka probaj napraviti registraciju korisnika
    if($isOK === TRUE)
    {
        //- Potraži da li je već registrirano korisničko ime
        $stmt = $global_database-> prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->bind_result($id);
        $stmt->fetch();
        $stmt->close();
        
        // Ako postoji, ne smije se dopustiti ponovna registracija, treba ispisati poruku da već postoji
        if($id > 0)
        {
            $error_msg  = $global_txt['exists_username'];
        }
        else //- provjeravamo isto i za email
        {
            //- Potraži da li je već registrirana email adresa
            $stmt = $global_database-> prepare("SELECT id FROM users WHERE email=?");
            $stmt->bind_param('s', $_POST['email']);
            $stmt->execute();
            $stmt->bind_result($id);
            $stmt->fetch();
            $stmt->close();
            
            // Ako postoji, ne smije se dopustiti ponovna registracija, treba ispisati poruku da već postoji
            if($id > 0)
            {
                $error_msg  = $global_txt['exists_email'];
            }
            else
            {
                // REGISTRACIJA KORISNIKA 
                $rights = "1"; // obični korisnik ima prava = 1, admin = 999
                $psw    = hash("sha256", $_POST['password']); // http://php.net/manual/en/function.hash.php - lozinka se hashira prije spremanja u bazu
                $stmt = $global_database->prepare("INSERT INTO `users` (`username`, `password`, `name`, `lastname`, `email`, `rights`) VALUES(?,?,?,?,?,?)");
                $stmt->bind_param('sssssi', $_POST['username'], $psw, $_POST['name'], $_POST['lastname'], $_POST['email'], $rights);
                $stmt->execute();
                $stmt->close();
                header('Location: ' . $global_url."views/register_success.php");
            }
        }
        
        
    }
            
}
    
//- HTML ispis na stranici
echo "<form id='register' action='".$global_url."views/register.php' method='POST'>";
    echo "<div class='error_msg'>";
        echo $error_msg;
    echo "</div>";
    
    echo "<div id='reg_mid'>";
        
        show_input_text_part($global_txt['username'],      "username",     "username",     $username,      TRUE,   "text");
        show_input_text_part($global_txt['password'],      "password",     "password",     "",             TRUE,   "password");
        show_input_text_part($global_txt['repassword'],    "repassword",   "repassword",   "",             TRUE,   "password");
        show_input_text_part($global_txt['name'],          "name",         "name",         $name,          FALSE,  "text");
        show_input_text_part($global_txt['lastname'],      "lastname",     "lastname",     $lastname,      FALSE,  "text");
        show_input_text_part($global_txt['email'],         "email",        "email",        $email,         TRUE,   "text");
        
        echo "<input type='submit' name='submit' value='".$global_txt['submit']."'></input>";
        
    echo "</div>";
    echo "<div id='reg_dwn'></div>";
echo "</form>";

//- Za formatiranje
function show_input_text_part($text, $id, $name, $value, $mandatory, $type)
{
    echo "<div class='reg_part'>";
        echo "<div class='reg_left'>";
            echo $text;
        echo "</div>";
        echo "<div class='reg_right'>";            
            echo "<input type='".$type."' id='".$id."' name='".$name."' value='".$value."'></input>";
            if($mandatory === TRUE)// označi da je obavezno polje
            {
                echo "*";
            }
        echo "</div>";
    echo "</div>";
}
include_once '../footer.php';
/* register.php */