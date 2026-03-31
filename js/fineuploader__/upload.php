<?php

$tipo = $_GET['tipo'];

// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;

if ($tipo == "logo") {
    $allowedExtensions = array("jpg", "JPG", "png", "PNG");
} else {
    $allowedExtensions = array("xls");    
}

require('server/php.php');
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

if ($tipo == "logo") {
    array_map('unlink', glob("../../immagini/logo/*"));
    $path = "../../immagini/logo/";
} else {
    array_map('unlink', glob("../../tmp/*"));
    $path = "../../tmp/";
}

// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$result = $uploader->handleUpload($path);

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>
