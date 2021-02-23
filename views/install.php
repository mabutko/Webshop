<?php 
$database = new mysqli("localhost", "root", "root");
if ($database -> connect_errno) {
    echo "Failed to connect to MySQL: " . $database ->connect_error;
    $database ->close();
    return;
}

echo "<!doctype html>";
echo "<html lang='hr'>";
    echo "<head>";
        echo "<meta charset='utf-8'>";
        echo "<title>Instalacija sustava</title>";
        echo "<link rel='stylesheet' href='../css/style.css' type='text/css'  media='Screen'/>";    
    echo "</head>";
    echo "<body>";
    
echo "Instalacija baze:<br/>";
// Kreira bazu webshop-a
$database->query("CREATE DATABASE IF NOT EXISTS `webshop`");
// Postavlja webshop bazu kao bazu za korištenje u upitima
$database->query("USE `webshop`");

// DROPAMO SVE TABLICE ako slučajno postoje 
$database->query("DROP TABLE IF EXISTS `comments`;");
$database->query("DROP TABLE IF EXISTS `cart_user`;");
$database->query("DROP TABLE IF EXISTS `cart`;");
$database->query("DROP TABLE IF EXISTS `products`;");
$database->query("DROP TABLE IF EXISTS `categories`;");
$database->query("DROP TABLE IF EXISTS `users`;");


// KREIRANJE TABLICA 
// 1. Pripremamo stringove sa CREATE statementima za tablice http://dev.mysql.com/doc/refman/5.1/en/create-table.html
$sql_users = "CREATE TABLE IF NOT EXISTS `users`(
    `id`            int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `username`      varchar(32)  DEFAULT NULL,
    `password`      varchar(256)  DEFAULT NULL,
    `name`          varchar(32)  DEFAULT NULL,
    `lastname`      varchar(32)  DEFAULT NULL,        
    `email`         varchar(32)  DEFAULT NULL,
    `rights`        int(2) unsigned NOT NULL ,
    `confirmed`     int(2) unsigned DEFAULT 0,
    `money`         double(9, 2) DEFAULT 1000,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica korisnika' AUTO_INCREMENT=1;";

$sql_categories = "CREATE TABLE IF NOT EXISTS `categories`(
    `category_id`   int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `category_name` varchar(32)  DEFAULT NULL,
    PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica kategorija' AUTO_INCREMENT=1;";

$sql_products = "CREATE TABLE IF NOT EXISTS `products`(
    `product_id`    int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `FK_product_category_id` int(21) unsigned NOT NULL, 
    `product_name`  varchar(32)     DEFAULT NULL,
    `product_price` double(9, 2)    DEFAULT NULL,
    `product_img`   varchar(32)     DEFAULT NULL,
    `product_desc`  varchar(256)    DEFAULT NULL,
    `product_size`  varchar(64)     DEFAULT NULL,
    `product_color` varchar(64)     DEFAULT NULL,
    `product_quantity` int(21)      DEFAULT 100,
    `product_hot`   int(1) DEFAULT 0,
    PRIMARY KEY (`product_id`),
    KEY `FK_product_category_id_key` (`FK_product_category_id`),
    CONSTRAINT `FK_product_category_id_key` FOREIGN KEY (`FK_product_category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica proizvoda' AUTO_INCREMENT=1;";

$sql_cart = "CREATE TABLE IF NOT EXISTS `cart`(
    `cart_id`        int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `FK_cart_user_id` int(21) unsigned NOT NULL,
    `cart_is_done`   int(1) unsigned NOT NULL DEFAULT 0,
    `cart_timestamp` DATETIME DEFAULT NULL,
    PRIMARY KEY (`cart_id`),
    KEY `FK_cart_user_id_key` (`FK_cart_user_id`),
    CONSTRAINT `FK_cart_user_id_key` FOREIGN KEY (`FK_cart_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica košarica za korisnika, ako je nešto već kupio dodaje se nova košarica a stara ima cart_is_done postavljen na 1 i upisan timestamp' AUTO_INCREMENT=1;";

$sql_cart_user = "CREATE TABLE IF NOT EXISTS `cart_user`(
    `cu_id`                 int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `FK_cu_cart_id`         int(21) unsigned NOT NULL, 
    `FK_cu_product_id`      int(21) unsigned NOT NULL,
    `cu_product_quantity`   int(21) unsigned NOT NULL DEFAULT 1,
    PRIMARY KEY (`cu_id`),
    KEY `FK_cu_cart_id_key` (`FK_cu_cart_id`),
    CONSTRAINT `FK_cu_cart_id_key` FOREIGN KEY (`FK_cu_cart_id`) REFERENCES `cart` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `FK_cu_product_id_key` (`FK_cu_product_id`),
    CONSTRAINT `FK_cu_product_id_key` FOREIGN KEY (`FK_cu_product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica veze između cart_user i products ' AUTO_INCREMENT=1;";

$sql_comments = "CREATE TABLE IF NOT EXISTS `comments`(
    `comment_id`            int(21) unsigned NOT NULL AUTO_INCREMENT, 
    `FK_comment_user_id`    int(21) unsigned NOT NULL, 
    `FK_comment_product_id` int(21) unsigned NOT NULL,
    `comment`               varchar(1024) DEFAULT NULL,
    `comment_rating`        int(1) unsigned DEFAULT 0, 
    `comment_timestamp`     DATETIME DEFAULT NULL,
    PRIMARY KEY (`comment_id`),
    KEY `FK_comment_user_id_key` (`FK_comment_user_id`),
    CONSTRAINT `FK_comment_user_id_key` FOREIGN KEY (`FK_comment_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    KEY `FK_comment_product_id_key` (`FK_comment_product_id`),
    CONSTRAINT `FK_comment_product_id_key` FOREIGN KEY (`FK_comment_product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8' COLLATE='utf8_bin' COMMENT='Tablica komentara.' AUTO_INCREMENT=1;";

// 2. Kreiranje tablica u bazi, odnonso izvođenje CREATE statementa tablica te ispis poruke o statusu (uspiješno/neuspiješno kreirana tablica).
if($database->query($sql_users) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica users.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice users.</div>";

if($database->query($sql_categories) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica categories.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice categories.</div>";

if($database->query($sql_products) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica products.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice products.</div>";

if($database->query($sql_cart) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica cart.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice cart.</div>";

if($database->query($sql_cart_user) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica cart_user.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice cart_user.</div>";

if($database->query($sql_comments) === TRUE)
    echo "<div class='inst_success'>Uspiješno kreirana tablica comments.</div>";
else
    echo "<div class='inst_error'>Pogreška prilikom kreiranja tablice comments.</div>";

// 3. INSERT - dodajemo defaultnog korisnika, administratora sustava. Password se u bazu sprema kriptiran md5 enkripcijom
$psw = hash("sha256", "admin");
$sql_insert_users = "INSERT INTO `users` (`id`, `username`, `password`, `name`, `lastname`, `email`, `rights`, `confirmed`) 
                    VALUES ('1', 'admin', '".$psw."', 'admin', 'admin', 'admin@admin.com', '999', '1')";
                  
if($database->query($sql_insert_users) > 0)
    echo "<div class='inst_success'>Uspiješno dodan administrator.</div>";
else
    echo "<div class='inst_error'>Pogreška! Administrator nije dodan u bazu.</div>";

echo "Instalacija baze završena.<br/>";

    echo "</body>";
echo "</html>";
/* End of file install.php */