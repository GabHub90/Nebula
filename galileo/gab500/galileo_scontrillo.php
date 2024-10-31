<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/STRILLO_movimenti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/STRILLO_incassi.php');

class galileoScontrillo extends galileoOps {

    function __construct() {

        $this->tabelle['STRILLO_movimenti']=new strillo_movimenti();
        $this->tabelle['STRILLO_incassi']=new strillo_incassi();
    }

    function getMovimenti($arr) {

        $this->query="SELECT
            mov.*,
            incasso.*
        ";

        $this->query.=" FROM ".$this->tabelle['STRILLO_movimenti']->getTabName()." AS mov";
        $this->query.=" INNER JOIN ".$this->tabelle['STRILLO_incassi']->getTabName()." AS incasso ON incasso.movimento=mov.ID";

        $this->query.=" WHERE mov.cassa='".$arr['cassa']."' AND mov.chiusura='".$arr['chiusura']."'";

        $this->query.=" ORDER BY mov.reparto,mov.d_fatt,mov.ID,incasso.pos";
    }

    function registraMovimento($arr) {

        //TRANSACTION TRUE
        $this->disableIncrement('STRILLO_movimenti');

        $this->query='DECLARE @mov INT;';

        $this->query.='SELECT @mov = ISNULL(MAX(ID), 0) + 1 FROM '.$this->tabelle['STRILLO_movimenti']->getTabName().';';

        $arr['ID']='@mov';

        $res=$this->doInsert('STRILLO_movimenti',$arr,'','query');

        if (!$res) return false;

        foreach ($arr['incassi'] as $k=>$i) {

            $b=array(
                'movimento'=>'@mov',
                "pos"=>$k,
                "importo"=>$i['val'],
                "incasso"=>$i['incasso']
            );

            $this->doInsert('STRILLO_incassi',$b,'','query');
        }

        return true;
    }

}


?>