<?php
include './library/controllo.php';

session_destroy();
setcookie("cookieid", MD5($res["id"]), time()-1);

header("Location:./index.php");

?>