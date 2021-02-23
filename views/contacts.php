<?php
include_once '../header.php';
show_user_part();
show_menu_user("contacts.php");
global $global_url;
global $global_txt;
global $global_database;

$error_msg  = "";   //- varijabla za poruku o pogrešci, inicijalno nema pogreške
$name       = "";   //- ime 
$lastname   = "";   //- prezime
$email      = "";   //- email adresa
$subject    = "";   //- naslov maila
$comment    = "";   //- tekst maila
$email_to   = "admin@admin.hr";

//- ako je stisnuto dugme za slanje forme:
if(isset($_POST['submit']))
{
    $name       = $_POST['name'];
    $lastname   = $_POST['lastname'];
    $email      = $_POST['email'];
    $subject    = $_POST['subject'];
    $comment    = $_POST['comment'];
    $isOK       = TRUE;
    
    //- Provjerava se da li je email ispravnog formata - http://php.net/manual/en/function.filter-var.php
    if(filter_var( $_POST['email'], FILTER_VALIDATE_EMAIL ) === FALSE)
    {
        $error_msg = $global_txt['email_error'];
        $isOK       = FALSE;
    }
    //- provjerava obvezna polja
    if((empty($_POST['name']))||(empty($_POST['lastname']))||(empty($_POST['email']))||(empty($_POST['subject']))||(empty($_POST['comment'])))
    {
        $error_msg  = $global_txt['empty_error'];
        $isOK       = FALSE;
    }
    
    //- Ako nema pogrešaka probaj poslati e-mail
    if($isOK === TRUE)
    {
        // create email headers
        $headers = 'From: '.$email."\r\n".
        'Reply-To: '.$email."\r\n" .
        'X-Mailer: PHP/' . phpversion();

        //- email message
        $email_message = "Ime:".$name."\r\n";
        $email_message = "Prezime:".$lastname."\r\n";
        $email_message = "Email:".$email."\r\n";
        $email_message = "Poruka:".$comment."\r\n";
        @mail($email_to, $subject, $email_message, $headers); 

        header('Location: ' . $global_url."views/contacts_send.php");
    }
}
else //- ako prvi put dolazimo a registriran je korisnik, upiši njegove podatke u formu da ih ne mora popunjavati
{
    if(isset($_SESSION['user']))
    {
        $id = $_SESSION['user']['id'];
        $stmt = $global_database->prepare("SELECT name, lastname, email FROM users WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($name, $lastname, $email);
        $stmt->fetch();
        $stmt->close();
    }
}


//- HTML ispis na stranici
echo "<form id='contacts' action='".$global_url."views/contacts.php' method='POST'>";
    echo "<div class='error_msg'>";
        echo $error_msg;
    echo "</div>";
    
    echo "<div id='reg_mid'>";
        show_input_text_part($global_txt['name'],          "name",         "name",         $name,          TRUE,   "text");
        show_input_text_part($global_txt['lastname'],      "lastname",     "lastname",     $lastname,      TRUE,   "text");
        show_input_text_part($global_txt['email'],         "email",        "email",        $email,         TRUE,   "text");
        show_input_text_part($global_txt['subject'],       "subject",      "subject",      $subject,       TRUE,   "text");
        show_input_textarea_part($global_txt['comment'],   "comment",      "comment",      $comment,       TRUE);
        
        echo "<input type='submit' name='submit' value='".$global_txt['submit']."'></input>";
        
    echo "</div>";
    echo "<div id='reg_dwn'></div>";
echo "</form>";

//- Za prikaz input fielda
function show_input_text_part($text, $id, $name, $value, $mandatory, $type)
{
    echo "<div class='reg_part'>";
        echo "<div class='reg_left'>";
            echo $text;
        echo "</div>";
        echo "<div class='reg_right'>";            
            echo "<input type='".$type."' id='".$id."' name='".$name."' value='".$value."'></input>";
            if($mandatory === TRUE)// označi da je obavezno polje
                echo "*";
        echo "</div>";
    echo "</div>";
}

function show_input_textarea_part($text, $id, $name, $value, $mandatory)
{
    echo "<div class='reg_part'>";
        echo "<div class='reg_left'>";
            echo $text;
        echo "</div>";
        echo "<div class='reg_right'>";            
            echo "<textarea id='".$id."' name='".$name."' maxlength='1000' cols='50' rows='10'>".$value."</textarea>";
            if($mandatory === TRUE)// označi da je obavezno polje
                echo "*";
        echo "</div>";
    echo "</div>";
}

include_once '../footer.php';
/* contacts.php */