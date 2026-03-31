<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

$ateliers = getDati("utenti", "WHERE livello = '5' AND attivo = 1");
$utenti = getDati("utenti", "WHERE livello = '3' AND attivo = 1");
$list_ruoli = getRuoloUtente();
$atelier = $users = $ruoli = $stati = Array();
if ($_GET['id'] != "") {
    $mess = getDati("messaggi_app", "where id=" . $_GET['id'])[0];
    $cols = getDati("messaggio_atelier", "where idmessaggio=" . $_GET['id']);
    foreach ($cols as $col) {
        $atelier[] = $col['idatelier'];
    }
    $cols = getDati("messaggio_users", "where idmessaggio=" . $_GET['id']);
    foreach ($cols as $col) {
        $users[] = $col['idutente'];
    }
    $cols = getDati("messaggio_ruolo", "where idmessaggio=" . $_GET['id']);
    foreach ($cols as $col) {
        $ruoli[] = $col['ruolo'];
    }
    $cols = getDati("messaggio_stato", "where idmessaggio=" . $_GET['id']);
    foreach ($cols as $col) {
        $stati[] = $col['stato'];
    }
    //var_dump($atelier, $ruoli, $users, $stati);
} else {
    $mess = Array();
}
?>
<form method="post" action="" id="form-messaggio" name="formdipendenti">
    <input type="text" name="titolo_push" id="titolo_push" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Titolo notifica push" title="Titolo notifica push" value="<?= $mess['titolo_push'] ?>" /><div class="chiudi"></div>
    <input type="text" name="descrizione_push" id="descrizione_push" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Descrizione notifica push" title="Descrizione notifica push" value="<?= $mess['descrizione_push'] ?>" /><div class="chiudi"></div>
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Titolo messaggio" title="Titolo messaggio" value="<?= $mess['titolo'] ?>" />
    <div class="chiudi"></div>
    <select id="tipo" name="tipo" class="input_moduli float_moduli_small">
        <option value="0"<?= ($mess['tipo'] == 0 ? ' selected' : '') ?>>Generico</option>
        <option value="1"<?= ($mess['tipo'] == 1 ? ' selected' : '') ?>>Appuntamento</option>
        <option value="2"<?= ($mess['tipo'] == 2 ? ' selected' : '') ?>>Formazione</option>
    </select>
    <div class="chiudi"></div>
    <select id="idatelier" name="idatelier[]" class="input_moduli float_moduli_small" multiple>
        <option value="0">Tutti gli Atelier</option>
        <?php foreach ($ateliers as $col) { ?>
            <option value="<?= $col['id'] ?>"<?= ($mess['idatelier'] == $col['id'] || in_array($col['id'], $atelier) ? ' selected' : '') ?>><?= $col['nominativo'] ?></option>
        <?php } ?>
    </select>
    <select id="idutente" name="idutente[]" class="input_moduli float_moduli_small" multiple>
        <option value="0">Tutti gli Utenti</option>
        <?php foreach ($utenti as $col) { ?>
            <option value="<?= $col['id'] ?>"<?= ($mess['idutente'] == $col['id'] || in_array($col['id'], $users) ? ' selected' : '') ?>><?= $col['cognome'] ?> <?= $col['nome'] ?></option>
        <?php } ?>
    </select>
    <div class="chiudi" style="height: 20px;"></div>
    <select id="ruolo" name="ruolo[]" class="input_moduli float_moduli_small" multiple>
        <option value="-1">Tutti i ruoli</option>
        <?php foreach ($list_ruoli as $ruolo) { ?>
            <option value="<?= $ruolo['id'] ?>"<?= (in_array($ruolo['id'], $ruoli) ? ' selected' : '') ?>><?= $ruolo['nome'] ?></option>
        <?php } ?>>
    </select>
    <select id="stato" name="stato[]" class="input_moduli float_moduli_small" multiple>
        <option value="-1">Tutti gli stati</option>
        <option value="1"<?= (in_array(1, $stati) ? ' selected' : '') ?>>Formatore</option>
        <option value="2"<?= (in_array(2, $stati) ? ' selected' : '') ?>>in formazione</option>
        <option value="3"<?= (in_array(3, $stati) ? ' selected' : '') ?>>Superadmin</option>
    </select>
    <div class="chiudi" style="height: 20px;"></div>
    <input type="text" name="minuti_notifica" id="minuti_notifica" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Minuti notifica appuntamento" title="Minuti notifica appuntamento" value="<?= $mess['minuti_notifica'] ?>" />
    <div class="chiudi" style="height: 20px;"></div>
    <textarea name="descrizione" id="descrizione" class="textarea_moduli sizing  float_moduli" placeholder="Testo del messaggio" title="Testo del messaggio"><?= $mess['descrizione'] ?></textarea>
    <div class="chiudi"></div>
    <input type="hidden" name="id" id="id" value="<?= $_GET['id'] ?>" />
    <input type="submit" id="submitformdipendenti" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraMessaggi();">Chiudi</a></div>
</form>
<script>
    $(function () {
        $('select#idatelier').select2({
            placeholder: 'Seleziona uno o piu\' Atelier'
        });
        $('select#idutente').select2({
            placeholder: 'Seleziona uno o piu\' Utenti'
        });
        $('select#ruolo').select2({
            placeholder: 'Seleziona uno o piu\' Ruoli'
        });
        $('select#stato').select2({
            placeholder: 'Seleziona uno o piu\' Stati'
        });
    });
</script>