<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/CARB_buoni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CARB_causali.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CARB_caurep.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/CARB_responsabili.php');

include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_sedi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_reparti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_collaboratori.php');

class galileoCarb extends galileoOps {

    function __construct() {

        $this->tabelle['CARB_buoni']=new carb_buoni();
        $this->tabelle['CARB_causali']=new carb_causali();
        $this->tabelle['CARB_caurep']=new carb_caurep();
        $this->tabelle['CARB_responsabili']=new carb_responsabili();
        $this->tabelle['MAESTRO_sedi']=new maestro_sedi();
        $this->tabelle['MAESTRO_reparti']=new maestro_reparti();
        $this->tabelle['MAESTRO_collaboratori']=new maestro_collaboratori();
    }

    function getResponsabili($arr) {

        $this->query="SELECT
            res.*,
            coll.ID
        ";

        $this->query.=" FROM ".$this->tabelle['CARB_responsabili']->getTabName()." AS res";
        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS coll ON res.coll=coll.concerto";

        $this->query.=" WHERE res.stato='1'";

        return true;
    }

    function lizard_buoni($arr) {

        $this->query="SELECT
            b.ID,
            b.stato,
            b.d_stampa,
            b.importo,
            b.tipo_carb as carburante,
            b.reparto as cod_reparto,
            rep.descrizione as des_reparto,
            cau.causale,
            c_rich.nome+' '+c_rich.cognome AS richiedente,
            b.nota,
            CASE
                WHEN b.gestione='TANICA' THEN 'TANICA'
                ELSE b.targa
            END AS targa,
            b.telaio,
            b.des_veicolo,
            b.d_creazione,
            c_esec.nome+' '+c_esec.cognome AS operatore,
            isnull(b.d_ris,'') AS d_ris,
            c_ris.nome+' '+c_ris.cognome AS operatore_ris,
            b.nota_ris,
            isnull(b.d_annullo,'') as d_annullo,
            c_annullo.nome+' '+c_annullo.cognome AS operatore_annullo,
            b.nota_annullo
        ";

        $this->query.=" FROM ".$this->tabelle['CARB_buoni']->getTabName()." AS b";
        $this->query.=" inner join ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS rep ON b.reparto=rep.tag";
        $this->query.=" left join ".$this->tabelle['CARB_causali']->getTabName()." AS cau ON b.causale=cau.codice";
        $this->query.=" left join ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS c_rich ON b.id_rich=c_rich.ID";
        $this->query.=" left join ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS c_esec ON b.id_esec=c_esec.ID";
        $this->query.=" left join ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS c_ris ON b.id_ris=c_ris.ID";
        $this->query.=" left join ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS c_annullo ON b.id_annullo=c_annullo.ID";

        $this->query.=" WHERE isnull(b.d_stampa,'')!='' AND b.d_stampa>='".$arr['da']."' AND b.d_stampa<='".$arr['a']."' AND rep.sede='".$arr['sede']."'";

        return true;
    }

} 


?>