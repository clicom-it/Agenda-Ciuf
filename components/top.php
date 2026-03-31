<?php
$nomepagina = basename($_SERVER['PHP_SELF']);

if ($nomepagina == "mrgest.php") {
    $age = "linguetta_active";
} else if ($nomepagina == "impostazioni.php") {
    $imp = "linguetta_active";
} else if ($nomepagina == "dipendenti.php") {
    $dip = "linguetta_active";
} else if ($nomepagina == "clientifornitori.php") {
    $cliforn = "linguetta_active";
} else if ($nomepagina == "commesse.php") {
    $com = "linguetta_active";
} else if ($nomepagina == "preventivi.php") {
    $pre = "linguetta_active";
} else if ($nomepagina == "fatture.php") {
    $fat = "linguetta_active";
} else if ($nomepagina == "scadenziario.php") {
    $sca = "linguetta_active";
} else if ($nomepagina == "partitario.php") {
    $par = "linguetta_active";
} else if ($nomepagina == "ore.php") {
    $ore = "linguetta_active";
} else if ($nomepagina == "statistiche.php") {
    $sta = "linguetta_active";
} else if ($nomepagina == "magazzino.php") {
    $mag = "linguetta_active";
} else if ($nomepagina == "ddt.php") {
    $ddt = "linguetta_active";
} else if ($nomepagina == "budget.php") {
    $bud = "linguetta_active";
} else if ($nomepagina == "ecommerce.php") {
    $eco = "linguetta_active";
}
?>
<div class="bkg_black_loading"></div>
<div class="contboxcambialogin sizing" id="boxcambialogin">
    <div class="arrowup"><img src="/immagini/arrowup.png" /></div>
    <div class="chiudi"></div>
    <div class="box_slide sizing">
        Cambia dati di Login<br /><br />
        <form method="post" action="" name="formcambialogin" id="formcambialogin">
            <input type="hidden" name="cambiaid" id="cambiaid" value="<?php echo $_SESSION['id']; ?>" />
            <input class="input_login sizing required" placeholder="E-mail" style="background-image:url('./immagini/user.png'); margin-top: 0px;" type="email" name="cambiauser" id="cambiauser" value="<?php echo $_SESSION['username']; ?>" />
            <input class="input_login sizing" placeholder="Password" style="background-image:url('./immagini/pass.png'); margin-top: 10px;" type="password" name="cambiapassword" id="cambiapassword" />
            <input type="submit" name="submit" value="MODIFICA" id="cambialogin" class="submit_login sizing" style="margin-top: 10px;" />
            <div class="noerrore sizing" id="noerrorecambialogin" style="font-size: 1em;"></div>
        </form>
    </div>
</div>
<div class="top_black sizing">
    <a href="/mrgest.php"><img src="/immagini/logo-come-in-una-favola-2026-white-trasp-v2.png" style="height: 60px;margin: 10px 0;" /></a>
    <div class="float_rgt sizing" style="margin-top: 6px;">
        <a href="javascript:;" onclick="//javascript:mostradiv('boxcambialogin');" style="float: left;">
            <div class="icouser sizing"><i class="fa fa-user fa-2x" aria-hidden="true"></i></div>
            <div class="utente sizing"><?php echo $_SESSION["nome"] . " - " . $_SESSION["username"]; ?></div>                    
        </a>
        <div class="logout"><a href="/logout.php">LOGOUT</a></div>
    </div>
</div>
<div class="banda_ico sizing">
    <div class="linguette_mod sizing">
         <a href="/mrgest.php">
            <div class="linguetta sizing <?php echo $age; ?>">
                <i class="fa fa-calendar fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Agenda
            </div>
        </a>
        <?php if (moduloattivo("impostazioni") > 0) { ?>
            <a href="/impostazioni.php">
                <div class="linguetta sizing <?php echo $imp; ?>">
                    <i class="fa fa-cogs fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Impostazioni
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("dipendenti") > 0 || $_SESSION['ruolo'] == CENTRALINO || $_SESSION['ruolo'] == RISORSE_UMANE || ($_SESSION['livello'] == '5' /*&& ($_SESSION["diraff"] == 'd' || $_SESSION['id'] == 55)*/)) { ?>
            <a href="/dipendenti.php">
                <div class="linguetta sizing <?php echo $dip; ?>">
                    <i class="fa fa-shopping-bag fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Atelier
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("clientifornitori") > 0) { ?>
            <a href="/clientifornitori.php">
                <div class="linguetta sizing <?php echo $cliforn; ?>">
                    <i class="fa fa-users fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Clienti
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("domini") > 0) { ?>
            <a href="/domini.php">
                <div class="linguetta sizing <?php echo $dom; ?>">
                    <i class="fa fa-globe fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Domini
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("magazzino") > 0) { ?>
            <a href="/magazzino.php">
                <div class="linguetta sizing <?php echo $mag; ?>">
                    <i class="fa fa-archive fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Magazzino
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("ddt") > 0) { ?>
            <a href="/ddt.php">
                <div class="linguetta sizing <?php echo $ddt; ?>">
                    <i class="fa fa-road fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />DDT
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("preventivi") > 0) { ?>
            <a href="/preventivi.php">
                <div class="linguetta sizing <?php echo $pre; ?>">
                    <i class="fa fa-file-text fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Preventivi
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("commesse") > 0) { ?>
            <a href="/commesse.php">
                <div class="linguetta sizing <?php echo $com; ?>">
                    <i class="fa fa-pie-chart fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Commesse
                </div>
            </a>      
        <?php } ?>
        <?php if (moduloattivo("ecommerce") > 0) { ?>
            <a href="/ecommerce.php">
                <div class="linguetta sizing <?php echo $eco; ?>">
                    <i class="fa fa-shopping-bag fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />E-commerce
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("fatture") > 0) { ?>
            <a href="/fatture.php">
                <div class="linguetta sizing <?php echo $fat; ?>">
                    <i class="fa fa-list fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Fatture
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("scadenziario") > 0) { ?>
            <a href="/scadenziario.php">
                <div class="linguetta sizing <?php echo $sca; ?>">
                    <i class="fa fa-calendar fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Scadenziario
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("partitario") > 0) { ?>
           <a href="/partitario.php">
                <div class="linguetta sizing <?php echo $par; ?>">
                    <i class="fa fa-calculator fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Partitario
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("ore") > 0) { ?>
            <a href="/ore.php">
                <div class="linguetta sizing <?php echo $ore; ?>">
                    <i class="fa fa-clock-o fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Ore
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("statistiche") > 0 || $_SESSION['ruolo'] == CENTRALINO) { ?>
            <a href="/statistiche.php">
                <div class="linguetta sizing <?php echo $sta; ?>">
                    <i class="fa fa-bar-chart fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Statistiche
                </div>
            </a>
        <?php } ?>
        <?php if (moduloattivo("budget") > 0 || $_SESSION['livello'] == 3 || $_SESSION['ruolo'] == RISORSE_UMANE || ($_SESSION['livello'] == '5' && ($_SESSION["diraff"] == 'd' || $_SESSION['id'] == 76))) { ?>
            <a href="/budget.php">
                <div class="linguetta sizing <?php echo $bud; ?>">
                    <i class="fa fa-line-chart fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Budget e Premi
                </div>
            </a>
        <?php } ?>
        <?php if ($_SESSION['livello'] <= 1 || $_SESSION['ruolo'] == DISTRICT_MANAGER || $_SESSION['ruolo'] == RISORSE_UMANE) { ?>
        <a href="https://sites.google.com/comeinunafavola.it/assunzioni-come-in-una-favola/home-page" target="_blank">
                <div class="linguetta sizing <?php echo $bud; ?>">
                    <i class="fa fa-folder fa-2x" aria-hidden="true" style="color: #ffffff;"></i><br />Documenti Assunzione
                </div>
            </a>
        <?php } ?>
    </div>
</div>