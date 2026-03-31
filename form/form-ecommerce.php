<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

/* richiamo dati connessione ecommerce da database */

$dati = getDati("conf_ecommerce", "");
foreach ($dati as $datigest) {
    $id = $datigest['id'];
    $host_db = $datigest['host_db'];
    $nome_db = $datigest['nome_db'];
    $usr_db = $datigest['usr_db'];
    $pass_db = $datigest['pass_db'];
}


$submit = "";
if (isset($_POST['submit'])) {
    $submit = $_POST['submit'];
}
?>


<div class="testo_import">
    <strong>DATI CONNESSIONE E-COMMERCE</strong><br />
</div>
<form method="post" action="" id="formecommerce" name="formecommerce">        

    <input type="text" name="host_db" id="host_db" class="input_moduli sizing float_moduli_small_25 required" placeholder="Host db" title="Host db" value="<?php echo $host_db; ?>" />
    <input type="text" name="nome_db" id="nome_db" class="input_moduli sizing float_moduli_small_25 required" placeholder="Nome db" title="Nome db" value="<?php echo $nome_db; ?>" />
    <input type="text" name="usr_db" id="usr_db" class="input_moduli sizing float_moduli_small_25 required" placeholder="User db" title="User db" value="<?php echo $usr_db; ?>" /> 
    <input type="password" name="pass_db" id="pass_db" class="input_moduli sizing float_moduli_small_25 nopost" placeholder="Password db" title="Password db" value="" /> 

    <div class="chiudi"></div>
    <input type="submit" id="submitformecommerce" value="Salva" class="submit_form nopost" />
    <div class="chiudi"></div>
</form>