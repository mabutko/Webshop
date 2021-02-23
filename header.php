<?php 
include_once 'hrv.php'; // dodajemo tekstove programa
global $global_txt;     // Polje za tekstove iz hrv.php
// - URL do web stranica, obavezno slash na kraju!
$global_url = "http://localhost/";
/////////////////////////////////////////////////////////////////////////////////////////////////
/* POSTAVLJANJE SESSIONA */
// SESSION postavke - http://php.net/manual/en/function.session-name.php
session_name("webshop");
session_start();
// Postavljanje vremenske zone - http://php.net/manual/en/function.date-default-timezone-set.php
date_default_timezone_set("Europe/Zagreb");
/////////////////////////////////////////////////////////////////////////////////////////////////
/* SPAJANJE NA BAZU */
$global_database = new mysqli("localhost", "root", "root");
if ($global_database -> connect_errno) {
    echo "Failed to connect to MySQL: " . $global_database ->connect_error;
    $global_database ->close();
    return;
}

// Postavlja se encoding 
$global_database -> set_charset('utf8');
// AUTOCOMMIT - automatsko pisanje u bazu nakon svake naredbe postavljamo na TRUE inače bi morali pozivati commit funkciju nakon svakog INSERT,UPDATE,DELETE statementa.  http://en.wikipedia.org/wiki/Autocommit
// Postavlja se na FALSE npr. kada se želi niz naredbi upisati u bazu ali kada je potrebno da se upiše ili sve ili ništa. Sve se izvodi u memoriji a piše se u bazu tek kada se pozove commit funkcija. Transakcijski način rada koji omogućava ROLLBACK.
$global_database -> autocommit(TRUE); 
// Konekciji se govori koju bazu treba koristiti
$global_database -> query ("USE `webshop`;");
/////////////////////////////////////////////////////////////////////////////////////////////////
echo "<!doctype html>";
echo "<html lang='hr'>";
    echo "<head>";
        echo "<meta charset='utf-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=yes'/>";
        echo "<link rel='stylesheet' href='".$global_url."css/style.css' type='text/css'  media='Screen'/>";    
        echo "<title>".$global_txt['title']."</title>";
        echo "<meta name='description' content='Webshop, online kupnja'>";
        echo "<meta name='author' content=''>";
    echo "</head>";

        echo "<body>";
            echo "<div id='wrapper'>";
                echo "<div id='main'>";

       
///////////////////////////////////////////////////////////////////////////////////////////////
// KORISNIČKI DIO - prijava, registracija ako nije prijavljen korisnik inače username i logout button
/* Prikaz podataka o korisniku ako je spojen ili ponuda za registraciju/prijavu u sustav */
function show_user_part()
{
    global $global_url;
    global $global_txt;
    if(isset($_SESSION['user']))
    {
        echo "<div id='user_part'>";
            echo "<span id='logo'></span>";
            echo "<div id='login-wrapper'>";
            echo $_SESSION['user']['username'];
            echo " ";
            echo "<a  href='".$global_url."views/logout.php'>";
                echo $global_txt['logout'];
            echo "</a>";
        echo "</div>";
        echo "</div>";
    }
    /* Inače prikaži login i register opcije*/
    else
    {
        echo "<div id='user_part'>";
        echo "<span id='logo'></span>";
        echo "<div id='login-wrapper'>";
            echo "<a href='".$global_url."views/login.php'>";
                echo $global_txt['login'];
            echo "</a>";
            echo " ";
            echo "<a href='".$global_url."views/register.php'>";
                echo $global_txt['register'];
            echo "</a>";
        echo "</div>";
        echo "</div>";
    }
}
///////////////////////////////////////////////////////////////////////////////////////////////
//- IZBORNICI PROGRAMA
///////////////////////////////////////////////////////////////////////////////////////////////
// GLAVNI IZBORNIK
function show_menu_user($current_page)
{
    global $global_txt;
    
    //- provjeravamo je li admin da znamo treba li admin menu prikazati
    $isAdmin = FALSE;
    if(isset($_SESSION['user']))
    {
        if($_SESSION['user']['rights'] == '999')
        {
            $isAdmin = TRUE;
        }
    }
    
    echo "<div id='menu'>"; 
        show_menu_item("homepage.php", $global_txt['menu_home'],       $current_page);
        show_menu_item("products.php", $global_txt['menu_products'],   $current_page);
        //- ako je korisnik registriran prikaži i izbornik za korisničke stranice
        if(isset($_SESSION['user']))
        {
            if($isAdmin === FALSE)//korisniku, ali ne i adminu
            {
                show_cart("cart.php",           $global_txt['menu_cart'],          $current_page);
            }
            show_menu_item("history.php",   $global_txt['menu_user_history'],  $current_page);
        }
        // adminu prikaži izbornik za administraciju
        if($isAdmin === TRUE)
        {
            show_menu_item("admin_products.php", $global_txt['menu_administration'],   $current_page);
        }
        else // korisnik ima izbornik za kontakt, nema smisla ga prikazati adminu
        {
            show_menu_item("contacts.php", $global_txt['menu_contact'],                $current_page);
        }
       
    echo "</div>"; //- kraj #menu diva
    echo "<div id='main-content'>"; 
}
// ADMINISTRATORSKI IZBORNIK    
function show_menu_admin($current_page)
{
    global $global_txt;
    echo "<div id='menu'>";
        show_menu_item("homepage.php",          $global_txt['menu_home'],              $current_page);
        show_menu_item("admin_products.php",    $global_txt['menu_admin_products'],    $current_page);
        show_menu_item("admin_categories.php",  $global_txt['menu_admin_categories'],  $current_page);
        show_menu_item("admin_users.php",       $global_txt['menu_admin_users'],       $current_page);
    echo "</div>"; //- kraj #menu diva
    echo "<div id='main-content'>"; 
}
                

/* Pomoćna funkcija za iscrtavanje menu itema */
function show_menu_item($link, $item_name, $page_name)
{
    global $global_url;
    $cls = '';
    if($page_name == $link)
        $cls = "sel";
    
    echo "<div class='menu_item'>";
        echo "<a href='".$global_url."views/".$link."' class='".$cls."'>";
            echo $item_name;
        echo "</a>";
    echo "</div>";
}
/* Funkcija za iscrtavanje menu itema košarice, posebno napravljena jer se spaja na bazu i vraća broj artikala, također drugačije je prikazana od ostalih menu itema */
function show_cart($link, $item_name, $page_name)
{
    $count = 0;
    global $global_url;
    global $global_database;
    // zamijeni COUNT sa SUM ako želiš količinu proizvoda, npr. ako imamo jednu majicu sa količinom 3 sa count će ispisati 1(kao 1 proizvod), sa SUM će ispisati 3
    $stmt = $global_database->prepare("SELECT COUNT(cu_product_quantity) FROM cart JOIN cart_user ON cart_user.FK_cu_cart_id = cart.cart_id  WHERE  FK_cart_user_id=? AND cart_is_done = 0");
    
    $stmt->bind_param('i', $_SESSION['user']['id']);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
           
    $cls = '';
    if($page_name == $link)
        $cls = "sel";
    
    echo "<div class='menu_item'>";
        echo "<a href='".$global_url."views/".$link."' class='".$cls."'>";
            echo $item_name;
            echo "(".$count.")";
        echo "</a>";
    echo "</div>";
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/**
 * STRANIČENJE
 * Izračuni za straničenje
 * 
 * @param int $per_page     - koliko redova se prikazuje po stranici
 * @param int $num_of_rec   - broj redova vraćen iz baze
 * @param int $cur_page     - trenutna stranica
 * @param int $pages        - vraćamo broj stranica
 * @param int $start        - početno za sql limit
 * @param int $end          - završno za sql limit
 */
function pagination($per_page, $num_of_rec, &$cur_page, &$number_of_pages, &$start, &$end)
{
    $number_of_pages = 1;
    if ($num_of_rec > 0)
    {
        $number_of_pages = (int)($num_of_rec / (float)($per_page));
        // Svaki put kad nije modulo jednak 0 dodaj jednu stranicu 
        // jer znači ili da smo na prvoj stranici pa je $pages = 0
        // (svejedno treba tu stranicu prikazati pa moramo povećati za 1) 
        //  ili npr. imamo više 15 recorda a prikazuje se po 10, 
        //  dobiti ćemo 1 i moramo uvećati za još 1 da korisnik može doći na stranicu 2
        if (($num_of_rec % (float)$per_page) != 0)
            $number_of_pages = $number_of_pages + 1;
    }   
    
    /* Računamo je li stranica koju trebamo prikazati unutar broja stranica */
    if($cur_page <= 0)
        $cur_page = 1;
    if($cur_page > $number_of_pages)
        $cur_page = $number_of_pages;
    
    /* kada znamo stranicu, onda znamo i od kuda do kuda prikazujemo podatke */
    /* Početna, npr. ako vidimo 50str za 1 str je 0*50+0 =0, za 2str je 1*50 = 50, itd.*/ 
    $start = (($cur_page-1) * $per_page); 
    /* Zadnja je jednaka broju rekorda po stranici */
    $end   = $per_page;                                        
    
}

// Prikazuje pagination ispod tablice, slika i sl.
function show_pagination($url, $per_page, $cur_page, $number_of_pages, $num_of_rec, $get1="", $get2="", $get3="")
{
    // Ako je samo jedna stranica nema smisla prikazivati straničenje
    if($number_of_pages <= 1)
        return;
    
    //- ako imamo dodatne parametre za get
    $url_extension = "";
    if(!empty($get1))
        $url_extension .= "&".$get1;
    if(!empty($get1))
        $url_extension .= "&".$get2;
    if(!empty($get1))
        $url_extension .= "&".$get3;
    
    global $global_txt;
    echo "<div id='pagination'>";
        echo "<a class='pagination_page' href='".$url."?p=1&pp=".$per_page.$url_extension."'>first</a>";
        $pages_before = ($cur_page - 1);
        if($pages_before > 0)
        {
            $counter = 0;
            for($i = 1; $i <= $pages_before; $i++)
            {
                echo "<a class='pagination_page' href='".$url."?p=".$i."&pp=".$per_page.$url_extension."'>".$i."</a>";
                $counter++;
                if($counter > 2)
                    break;
            }
        }
        echo "<a class='pagination_page sel' href='".$url."?p=".$cur_page."&pp=".$per_page.$url_extension."'>".$cur_page."</a>";    
        $pages_after = ($number_of_pages - $cur_page);
        if($pages_after > 0)
        {
            $counter = 0;
            for($i = $cur_page+1; $i <= $cur_page+$pages_after; $i++)
            {
                echo "<a class='pagination_page' href='".$url."?p=".$i."&pp=".$per_page.$url_extension."'>".$i."</a>";
                $counter++;
                if($counter > 2)
                    break;
            }
        }
        echo "<a class='pagination_page' href='".$url."?p=".$number_of_pages."&pp=".$per_page.$url_extension."'>last</a>";
        echo "<div id='pagination_message'>";
            echo $global_txt['total_records']." ".$num_of_rec;
        echo "</div>";
    echo "</div>"; 
}
/* header.php */