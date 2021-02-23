<?php
global $global_url;
include_once "../header.php";
unset($_SESSION['user']);
header('Location: '.$global_url);

/* logout.php */