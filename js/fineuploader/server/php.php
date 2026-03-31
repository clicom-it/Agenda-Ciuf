<?php

include '../../library/controllo.php';
include '../../library/config.php';
include '../../library/connessione.php';
include '../../library/basic.class.php';
//include '../../library/settaggi.class.php';
include '../../library/functions.php';
/* * **************************************
  Example of how to use this uploader class...
  You can uncomment the following lines (minus the require) to use these as your defaults.

  // list of valid extensions, ex. array("jpeg", "xml", "bmp")
  $allowedExtensions = array();
  // max file size in bytes
  $sizeLimit = 10 * 1024 * 1024;

  require('valums-file-uploader/server/php.php');
  $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);

  // Call handleUpload() with the name of the folder, relative to PHP's getcwd()
  $result = $uploader->handleUpload('uploads/');

  // to pass data through iframe you will need to encode all html tags
  echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

  /***************************************** */

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path, $uploadDirectory, $ext, $nomefile) {
        global $directory; // radice gestionale
        global $dir; // directory del modulo per settaggi
        global $tipo; // immagine o file

        $input = fopen("php://input", "r");
        $temp = tmpfile();

        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($realSize != $this->getSize()) {
            return false;
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        /* nico cambio permessi al file uploadato */
        $percorso_err = $_SERVER['DOCUMENT_ROOT'] . $path;
        $percorso = str_replace("../../", "/" . $directory . "/", $percorso_err);
        chmod($percorso, 0755);
        if ($tipo == "img") {
            /* nico ridimensionamento img grande e creo la thumbnail */
            /* settaggi */
            $tabella = "settaggi";
            $settaggi = new settaggi($id, $tabella, $lang);
            $datisettaggi = $settaggi->showSettaggi();
            $settmb = "htmb$dir";
            $setimg = "himg$dir";
            for ($i = 0; $i < count($datisettaggi); $i++) {
                if ($datisettaggi[$i]['nome'] == $settmb) {
                    $htmb = $datisettaggi[$i]['valore'];
                }
                if ($datisettaggi[$i]['nome'] == $setimg) {
                    $himg = $datisettaggi[$i]['valore'];
                }
            }
            /* fine settaggi */
            list($src_width, $src_height) = getimagesize($percorso);
            if ($himg != 0 && $src_height > $himg) {
                ///////////// resize dell'immagine grande ////////////////
                if ($ext == ".jpg" OR $ext == ".JPG" OR $ext == ".jpeg" OR $ext == ".JPEG") {
                    header('Content-type: image/jpeg');
                    list($width, $height) = getimagesize($percorso);
                    $height_rid = $himg;
                    $width_rid = $height_rid * $width / $height;
                    $imgbigtemp = imagecreatetruecolor($width_rid, $height_rid);
                    $source = imagecreatefromjpeg($percorso);
                    imagecopyresampled($imgbigtemp, $source, 0, 0, 0, 0, $width_rid, $height_rid, $width, $height);
                    imagejpeg($imgbigtemp, $percorso, 100);
                } else if ($ext == ".png" OR $ext == ".PNG") {
                    header('Content-type: image/png');
                    list($width, $height) = getimagesize($percorso);
                    $height_rid = $himg;
                    $width_rid = $height_rid * $width / $height;
                    $imgbigtemp = imagecreatetruecolor($width_rid, $height_rid);
                    imagealphablending($imgbigtemp, true);
                    $transparent = imagecolorallocatealpha($imgbigtemp, 0, 0, 0, 127);
                    imagefill($imgbigtemp, 0, 0, $transparent);
                    $source = imagecreatefrompng($percorso);
                    imagecopyresampled($imgbigtemp, $source, 0, 0, 0, 0, $width_rid, $height_rid, $width, $height);
                    imagealphablending($imgbigtemp, false);
                    imagesavealpha($imgbigtemp, true);
                    imagepng($imgbigtemp, $percorso);
                }
            }
            /* creo la thumbnails */
            $percorsotmb = str_replace("../../", "/" . $directory . "/", $uploadDirectory);
            $thumbFile = $_SERVER['DOCUMENT_ROOT'] . $percorsotmb . "tmb_" . $nomefile;
            copy($percorso, $thumbFile);
            chmod($thumbFile, 0755);
            ///////////// resize dell'immagine thumbnail ////////////////
            if ($htmb != 0 && $src_height > $htmb) {
                if ($ext == ".jpg" OR $ext == ".JPG" OR $ext == ".jpeg" OR $ext == ".JPEG") {
                    header('Content-type: image/jpeg');
                    list($width, $height) = getimagesize($thumbFile);
                    $height_rid = $htmb;
                    $width_rid = $height_rid * $width / $height;
                    $thumb = imagecreatetruecolor($width_rid, $height_rid);
                    $source = imagecreatefromjpeg($thumbFile);
                    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width_rid, $height_rid, $width, $height);
                    imagejpeg($thumb, $thumbFile, 100);
                } else if ($ext == ".png" OR $ext == ".PNG") {
                    header('Content-type: image/png');
                    list($width, $height) = getimagesize($thumbFile);
                    $height_rid = $htmb;
                    $width_rid = $height_rid * $width / $height;
                    $thumb = imagecreatetruecolor($width_rid, $height_rid);
                    imagealphablending($thumb, true);
                    $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
                    imagefill($thumb, 0, 0, $transparent);
                    $source = imagecreatefrompng($thumbFile);
                    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $width_rid, $height_rid, $width, $height);
                    imagealphablending($thumb, false);
                    imagesavealpha($thumb, true);
                    imagepng($thumb, $thumbFile);
                }
            }
        }
        /* */
        die('{"success" : "true", "nomefile" : "' . $nomefile . '"}');
    }

    function getName() {
        return $_GET['qqfile'];
    }

    function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
        } else {
            throw new Exception('Getting content length is not supported.');
        }
    }

}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm {

    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) {
        if (!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path)) {
            return false;
        }
        return true;
    }

    function getName() {
        return $_FILES['qqfile']['name'];
    }

    function getSize() {
        return $_FILES['qqfile']['size'];
    }

}

class qqFileUploader {

    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    private $uploadName;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760) {
        $allowedExtensions = array_map("strtolower", $allowedExtensions);

        $this->allowedExtensions = $allowedExtensions;
        $this->sizeLimit = $sizeLimit;

        $this->checkServerSettings();

        if (isset($_GET['qqfile'])) {
            $this->file = new qqUploadedFileXhr();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new qqUploadedFileForm();
        } else {
            $this->file = false;
        }
    }

    public function getUploadName() {
        if (isset($this->uploadName))
            return $this->uploadName;
    }

    public function getName() {
        if ($this->file)
            return $this->file->getName();
    }

    private function checkServerSettings() {
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));

        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit) {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");
        }
    }

    private function toBytes($str) {
        $val = trim($str);
        $last = strtolower($str[strlen($str) - 1]);
        switch ($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }

    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE) {
        if (!is_writable($uploadDirectory)) {
            return array('error' => "Errore server: directory senza permessi di scrittura.");
        }

        if (!$this->file) {
            return array('error' => 'no file da uploadare.');
        }

        $size = $this->file->getSize();

        if ($size == 0) {
            return array('error' => 'File vuoto');
        }

        if ($size > $this->sizeLimit) {
            return array('error' => 'File troppo grande');
        }

        $pathinfo = pathinfo($this->file->getName());



        $filename = pulisciImmagine($pathinfo['filename']);
        //$filename = md5(uniqid());
        $ext = @$pathinfo['extension'];  // hide notices if extension is empty

        if ($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)) {
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File con estensione non valida, estensioni ammesse: ' . $these . '.');
        }

        $ext = ($ext == '') ? $ext : '.' . $ext;

        if (!$replaceOldFile) {
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . $ext)) {
                $filename .= rand(10, 99);
            }
        }

        $this->uploadName = $filename . $ext;

        if ($this->file->save($uploadDirectory . $filename . $ext, $uploadDirectory, $ext, $this->uploadName)) {
            return array('success' => true);
        } else {
            return array('error' => 'Non posso salvare il file caricato.' .
                'L\'upload &egrave; stato cancellato, o il server ha incontrato un problema');
        }
    }

}
