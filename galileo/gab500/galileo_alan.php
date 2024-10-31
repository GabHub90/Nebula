<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/ALAN_parametri.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/ALAN_timbrature.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/ALAN_timbrature_k.php');

class galileoAlan extends galileoOps {

    function __construct() {

        $this->tabelle['ALAN_parametri']=new alan_parametri();
        $this->tabelle['ALAN_timbrature']=new alan_timbrature();
        $this->tabelle['ALAN_timbrature_k']=new alan_timbrature_k();
    }

    function getTimbrature($arr) {

        $this->query="SELECT
            IDDIP,
            CONVERT(varchar(8),DATAO,112) AS d,
            CONVERT(varchar(5),DATAO,114) AS h,
            VERSOO,
            IDTIMBRATURA,
            isnull(forza_minuti,-1) AS forza_minuti 
        ";

        $this->query.=" FROM ".$this->tabelle['ALAN_timbrature']->getTabName();

        $this->query.=" WHERE IDDIP='".$arr['dipendente']."' AND CONVERT(varchar(8),DATAO,112)>='".$arr['da']."' AND CONVERT(varchar(8),DATAO,112)<='".$arr['a']."'";

        $this->query.=" ORDER BY DATAO";

        if (isset($arr['ordine_query']) && $arr['ordine_query']) $this->query.=' '.$arr['ordine_query'];

        return true;
    }

    function controllaUscita($arr) {
        //ritorna una timbratura di qualsiasi verso uguale o successiva ad un orario

        $this->query="SELECT
            TOP 1
            IDDIP,
            CONVERT(varchar(8),DATAO,112) AS d,
            CONVERT(varchar(5),DATAO,114) AS h,
            VERSOO,
            IDTIMBRATURA,
            isnull(forza_minuti,-1) AS forza_minuti 
        ";

        $this->query.=" FROM ".$this->tabelle['ALAN_timbrature']->getTabName();

        $this->query.=" WHERE IDDIP='".$arr['IDDIP']."' AND DATAO>='".$arr['data']."'";

        $this->query.=" ORDER BY DATAO";

        return true;
    }

} 


?>