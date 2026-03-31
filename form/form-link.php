<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

if ($_GET['id'] != "") {
    $mess = getDati("link_app", "where id=" . $_GET['id'])[0];
} else {
    $mess = Array();
}
?>
<form method="post" action="" id="form-link" name="formlink">
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Titolo" title="Titolo" value="<?=$mess['titolo']?>" /><div class="chiudi"></div>
    <input type="text" name="link" id="link" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Link" title="Link" value="<?=$mess['link']?>" /><div class="chiudi"></div>
    <textarea name="descrizione" id="descrizione" class="textarea_moduli sizing  float_moduli" placeholder="Testo del messaggio" title="Testo del messaggio"><?=$mess['descrizione']?></textarea><div class="chiudi"></div>    
    <div class="chiudi"></div>
    <input type="hidden" name="id" id="id" value="<?=$_GET['id']?>" />
    <input type="submit" id="submitformlink" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraLink();">Chiudi</a></div>
</form>