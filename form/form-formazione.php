<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';

if ($_GET['id'] != "") {
    $mess = getDati("formazione", "where id=" . $_GET['id'])[0];
    $ruolo = getDati("formazione_ruolo", "where idformazione=" . $_GET['id']);
    $ruoli = Array();
    foreach ($ruolo as $r) {
        $ruoli[] = $r['ruolo'];
    }
} else {
    $mess = $ruoli = Array();
    $mess['tipo_dipendente'] = 2;
}
?>
<form method="post" action="" id="form-formazione">
    <input type="text" name="titolo" id="titolo" class="input_moduli sizing  float_moduli required soloatelier" placeholder="Titolo modulo" title="Titolo messaggio" value="<?= $mess['titolo'] ?>" />
    <div class="chiudi"></div>
    <select id="ruolo" name="ruolo[]" class="input_moduli float_moduli_small" multiple="true">
        <?php
        $lista_ruoli = getDati("ruolo", "order by id;");
        ?>
        <?php foreach ($lista_ruoli as $ruolo) { ?>
            <option value="<?= $ruolo['id'] ?>"<?= (in_array($ruolo['id'], $ruoli) ? ' selected' : '') ?>><?= $ruolo['valore'] ?></option>
        <?php } ?>
    </select>
    <div class="chiudi"></div>
    <input type="hidden" name="id" id="id" value="<?= $_GET['id'] ?>" />
    <input type="submit" id="submitformformazione" value="Salva" class="submit_form nopost" />
    <div class="bottone_chiudi sizing"><a href="javascript:;" class="sizing" onclick="mostraFormazione();">Chiudi</a></div>
</form>
<script>
    $(function () {
        $('select#ruolo').select2({
            placeholder: 'Seleziona uno o piu\' ruoli'
        });
    });
</script>