<?php

include_once(DROOT.'/nebula/galileo/solari/tabs/TIMBRATURE.php');
include_once(DROOT.'/nebula/galileo/solari/tabs/ANAGRAFICO.php');

class solariDBstart extends galileoOps {

    function __construct() {

        $this->tabelle['TIMBRATURE']=new solari_timbrature();
        $this->tabelle['ANAGRAFICO']=new solari_anagrafico();
        
    }

    function importaTimbrature($rif) {

        $this->query="SELECT
            IDDIP,
            CONVERT(varchar(8),DATAO,112) AS d,
            CONVERT(varchar(5),DATAO,114) AS h,
            VERSOO,
            IDTIMBRATURA
        ";

        $this->query.=" FROM ".$this->tabelle['TIMBRATURE']->getTabName();

        $this->query.=" WHERE DATAO IS NOT NULL AND IDTIMBRATURA>'".$rif."'";

        $this->query.=" ORDER BY IDTIMBRATURA";

        return true;
    }

    function importaTimbratureHR($rif) {

        $this->query="SELECT
            tim.IDDIP,
            CONVERT(varchar(8),tim.DATAO,112) AS d,
            CONVERT(varchar(5),tim.DATAO,114) AS h,
            tim.VERSOO,
            tim.IDTIMBRATURA,
            ana.CODFISC
        ";

        $this->query.=" FROM ".$this->tabelle['TIMBRATURE']->getTabName()." AS tim";
        $this->query.=" INNER JOIN ".$this->tabelle['ANAGRAFICO']->getTabName()." AS ana ON tim.IDDIP=ana.ID";

        $this->query.=" WHERE DATAO IS NOT NULL AND IDTIMBRATURA>'".$rif."'";

        $this->query.=" ORDER BY IDTIMBRATURA";

        return true;
    }

} 


?>