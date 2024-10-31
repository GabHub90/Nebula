<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_collaboratori.php');

include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_responsabili.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_periodi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_corsi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_permessi.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_extra.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_sostituzioni.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_sposta.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/TEMPO_dettaglio_bgc.php');

class galileoTempo extends galileoOps {

    function __construct() {

        $this->tabelle['MAESTRO_collaboratori']=new maestro_collaboratori();

        $this->tabelle['TEMPO_responsabili']=new tempo_responsabili();
        $this->tabelle['TEMPO_periodi']=new tempo_periodi();
        $this->tabelle['TEMPO_corsi']=new tempo_corsi();
        $this->tabelle['TEMPO_permessi']=new tempo_permessi();
        $this->tabelle['TEMPO_extra']=new tempo_extra();
        $this->tabelle['TEMPO_sostituzioni']=new tempo_sostituzioni();
        $this->tabelle['TEMPO_sposta']=new tempo_sposta();
        $this->tabelle['TEMPO_dettaglio_bgc']=new tempo_dettaglio_bgc();
    }

    function getAllUncheckedPeriodi($arr) {

        $this->query="SELECT
            ev.*,
            coll.cognome,
            coll.nome
        ";

        $this->query.=" FROM ".$this->tabelle['TEMPO_periodi']->getTabName()." AS ev";

        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS coll ON ev.coll=coll.ID";

        $this->query.=" WHERE isnull(dat_conferma,'')=''";

        $this->query.=" ORDER BY coll,data_i";

        return true;

    }

    function getAllUncheckedEvents($arr) {

        $this->query="SELECT
            ev.*,
            coll.cognome,
            coll.nome
        ";

        $this->query.=" FROM ".$this->tabelle['TEMPO_'.$arr['tipo']]->getTabName()." AS ev";

        $this->query.=" INNER JOIN ".$this->tabelle['MAESTRO_collaboratori']->getTabName()." AS coll ON ev.coll=coll.ID";

        $this->query.=" WHERE isnull(dat_conferma,'')=''";

        $this->query.=" ORDER BY coll,data";

        return true;

    }

} 


?>