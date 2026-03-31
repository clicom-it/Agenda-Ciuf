<?php

class ore extends basic {

    public $id;
    public $tabella;

    function __construct($id, $tabella) {
        $this->id = $id;
        $this->tabella = $tabella;
    }

    function aggiungi($campi, $valori, $voci) {
        global $db;
        parent::aggiungi($campi, $valori);


        $idgiornoore = $db->lastInsertId();
        if ($voci) {
            $sql = $db->prepare("INSERT INTO ore_voci (idutente, nomecognomeutente, centro_costo, costo, idgiornoore, dataoredettaglio, idcomm, idvocecomm, dettvocecomm, descr, orelavorate, idprofit, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            /* ore lavorate commessa */
            $sql1 = $db->prepare("SELECT orelavorate, costo FROM ore_voci WHERE idcomm = ?");
            /* update ore lavorate su commessa */
            $sql2 = $db->prepare("UPDATE commesse SET orelavorate = ?, totalecosti = ?  WHERE id = ?");
            /**/
            for ($i = 0; $i < count($voci); $i++) {
                $sql->execute(array($voci[$i]['idutente'], $voci[$i]['nomecognomeutente'], $voci[$i]['centro_costo'], $voci[$i]['costo'], $idgiornoore, $voci[$i]['dataoredettaglio'], $voci[$i]['idcomm'], $voci[$i]['idvocecomm'], $voci[$i]['dettvocecomm'], $voci[$i]['descr'], $voci[$i]['orelavorate'], $voci[$i]['idprofit'], $voci[$i]['ordine']));
                /* calcolo ore lavorate commessa */

                $sql1->execute(array($voci[$i]['idcomm']));
                $res1 = $sql1->fetchAll();

                $orelavorate = array();
                $costoore = 0;
                foreach ($res1 as $row1) {
                    $orelavorate[] = $row1['orelavorate'];
                    $costoore += $row1['costo'];
                }
                $orelavoratecomm = sommaorelavorate($orelavorate);
                /* aggiorno totale costi commessa */
                /* tutti costi commessa da singole voci */
                $sql3 = $db->prepare("SELECT SUM(costo) as costotot FROM commesse_costi WHERE idcomm = ?");
                $sql3->execute(array($voci[$i]['idcomm']));
                $costototdavoci = $sql3->fetchColumn();

                /* update ore lavorate commessa */
                $sql2->execute(array($orelavoratecomm, $costototdavoci + $costoore, $voci[$i]['idcomm']));
            }
        }
    }

    function cancella() {
        global $db;
        parent::cancella();
        $sql = $db->prepare("DELETE FROM ore WHERE id = ?");
        $sql->execute(array($this->id));
        /* cancello preventivi collegati */
        $sql = $db->prepare("DELETE FROM ore_voci WHERE idgiornoore = ?");
        $sql->execute(array($this->id));
    }

    function richiama() {
        return parent::richiama();
    }

    function richiamavoci($where) {
        return parent::richiamaWhere($where);
    }

    function aggiorna($campi, $valori, $voci) {
        global $db;
        /* aggiorno ore */
        parent::aggiorna($campi, $valori);
        /* seleziono le voci precedenti */

        $commesseprecedenti = array();
        $commesseattuali = array();
        $sql = $db->prepare("SELECT idcomm FROM ore_voci WHERE idgiornoore = ?");
        $sql->execute(array($this->id));
        $res = $sql->fetchAll();
        foreach ($res as $row) {
            $commesseprecedenti[] = $row['idcomm'];
        }
        /* rimuovo voci */
        $sql = $db->prepare("DELETE FROM ore_voci WHERE idgiornoore = ?");
        $sql->execute(array($this->id));
        /* aggiorno voci */
        if ($voci) {
            $sql = $db->prepare("INSERT INTO ore_voci (idutente, nomecognomeutente, centro_costo, costo, idgiornoore, dataoredettaglio, idcomm, idvocecomm, dettvocecomm, descr, orelavorate, idprofit, ordine) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");

            /**/
            for ($i = 0; $i < count($voci); $i++) {
                $commesseattuali[] = $voci[$i]['idcomm'];
                $sql->execute(array($voci[$i]['idutente'], $voci[$i]['nomecognomeutente'], $voci[$i]['centro_costo'], $voci[$i]['costo'], $this->id, $voci[$i]['dataoredettaglio'], $voci[$i]['idcomm'], $voci[$i]['idvocecomm'], $voci[$i]['dettvocecomm'], $voci[$i]['descr'], $voci[$i]['orelavorate'], $voci[$i]['idprofit'], $voci[$i]['ordine']));
            }
        }
        $arraycommesse = array_merge($commesseprecedenti, $commesseattuali);
        $arraycommesseok = array_values(array_unique($arraycommesse));
        /* ore lavorate commessa */
        $sql1 = $db->prepare("SELECT orelavorate, costo FROM ore_voci WHERE idcomm = ?");
        /* update ore lavorate su commessa */
        $sql2 = $db->prepare("UPDATE commesse SET orelavorate = ?, totalecosti = ?  WHERE id = ?");
        /* calcolo ore lavorate commessa */
        for ($n = 0; $n < count($arraycommesseok); $n++) {
            $sql1->execute(array($arraycommesseok[$n]));
            $res1 = $sql1->fetchAll();
            $orelavorate = array();
            $costoore = 0;
            foreach ($res1 as $row1) {
                $orelavorate[] = $row1['orelavorate'];
                $costoore += $row1['costo'];
            }
            $orelavoratecomm = sommaorelavorate($orelavorate);

            /* tutti costi commessa da singole voci */
            $sql3 = $db->prepare("SELECT SUM(costo) as costotot FROM commesse_costi WHERE idcomm = ?");
            $sql3->execute(array($arraycommesseok[$n]));
            $costototdavoci = $sql3->fetchColumn();

            /**/
            /* update ore lavorate commessa */
            $sql2->execute(array($orelavoratecomm, $costoore + $costototdavoci, $arraycommesseok[$n]));
        }
    }

}
