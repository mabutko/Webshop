<?php 
include_once "../header.php";
show_menu_user("login.php");

global $global_url;
global $global_txt;
global $global_database;

$error_msg  = "";   //- varijabla za poruku o pogrešci, inicijalno nema pogreške
$username   = "";   //- varijabla za korisničko ime, ako je npr. samo lozinka kriva da ne mora opet upisivati korisničko ime. Lozinka se uvijek mora upisati!


//- Provjera da li je forma submitana, ako je onda provjeravamo podatke, inače smo prvi put otvorili stranicu pa prikazujemo praznu login formu
if(isset($_POST['submit']))
{
    $username = $_POST['username'];
    //- Lozinka i password moraju biti upisani, inače ispiši poruku o pogrešci i nema smisla provjeravati username i password
    if((empty($_POST['username']))||(empty($_POST['password'])))
        $error_msg = $global_txt['empty_username_or_psw'];
    else
    {
        //- Provjeri postoji li korisnik u bazi
        //- Priprema SQL upita
        $psw = hash("sha256", $_POST['password']);
        $stmt = $global_database->prepare("SELECT id, rights, confirmed FROM users WHERE username=? AND password=?");
        $stmt->bind_param('ss', $_POST['username'], $psw);
        $stmt->execute();
        $stmt->bind_result($id, $rights, $confirmed);
        $stmt->fetch();
        $stmt->close();
        //- Ako je uspiješno registriran korisnik, upisujemo u session i redirektamo na početnu stranicu
        if($id > 0)
        {
            // Ako korisnik nije potvrđen ispiši mu obavijest
            if($confirmed <= 0)
                $error_msg = $global_txt['user_not_confirmed'];
            else
            {
                session_start();
                $_SESSION['user'] = array();
                $_SESSION['user']['username'] = $username;
                $_SESSION['user']['id']       = $id;
                $_SESSION['user']['rights']   = $rights;
                session_write_close();

                // Redirekt na početnu stranicu
                header('Location: '.$global_url);
                return;
            }
        }
        else //- inače ide poruka da su upisani pogrešni podaci za prijavu
        {
            $error_msg = $global_txt['login_error'];
        }
    }
}



//- HTML ispis na stranici
echo "<form id='login' action='".$global_url."views/login.php' method='POST'>";
    echo "<div class='error_msg'>";
        echo $error_msg;
    echo "</div>";
    
    echo "<div id='login_mid'>";
        
        echo "<div class='login_left'>";
            echo $global_txt['username'];
        echo "</div>";
        echo "<div class='login_right'>";    
            echo "<input type='text' id='username' name='username' value='".$username."'></input>";
        echo "</div>";
    
        echo "<div class='login_left'>";
            echo $global_txt['password'];
        echo "</div>";
        echo "<div class='login_right'>";            
            echo "<input type='password' id='password' name='password'></input>";
        echo "</div>";
        echo "</br>";
        echo "<input type='submit' name='submit' value='".$global_txt['login']."'></input>";
        
    echo "</div>";
    echo "<div id='login_dwn'></div>";
echo "</form>";

include_once "../footer.php";
/* login.php */