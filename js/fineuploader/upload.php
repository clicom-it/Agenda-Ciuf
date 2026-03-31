<?php

$id = $_GET['id'];

//$allowedExtensions = array('pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'xls', 'XLS', 'xlsx', 'XLXS', 'zip', 'ZIP');
$allowedExtensions = array('pdf', 'PDF', 'doc', 'DOC', 'docx', 'DOCX', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG');

// max file size in bytes
$sizeLimit = 10 * 1024 * 1024;

require('server/php.php');
$uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

$path = "../../appuntamenti/$id/";


// Call handleUpload() with the name of the folder, relative to PHP's getcwd()
$result = $uploader->handleUpload($path);

// to pass data through iframe you will need to encode all html tags
echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
?>
