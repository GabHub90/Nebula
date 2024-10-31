<?php
include_once(DROOT.'/nebula/galileo/gab500/tabs/DUDU_lines.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/DUDU_link.php');

class galileoDudu extends galileoOps {

    function __construct() {

        $this->tabelle['DUDU_lines']=new dudu_lines();
        $this->tabelle['DUDU_link']=new dudu_link();
    }

    function loadLink($arr) {

        $this->query="SELECT
            l.ID,
            l.riga,
            l.testo,
            isnull(l.d_creazione,'') AS d_creazione,
            isnull(l.d_scadenza,'') AS d_scadenza,
            isnull(l.d_chiusura,'') AS d_chiusura
        ";

        $this->query.=" FROM ".$this->tabelle['DUDU_lines']->getTabName()." as l";
        $this->query.=" INNER JOIN ".$this->tabelle['DUDU_link']->getTabName()." as link ON l.ID=link.dudu AND link.app='".$arr['app']."' AND link.rif='".$arr['rif']."'";
        $this->query.=" ORDER BY l.riga";

        return true;
    }

    function loadLines($arr) {

        $this->query="SELECT
            l.ID,
            l.riga,
            l.testo,
            isnull(l.d_creazione,'') AS d_creazione,
            isnull(l.d_scadenza,'') AS d_scadenza,
            isnull(l.d_chiusura,'') AS d_chiusura
        ";

        $this->query.=" FROM ".$this->tabelle['DUDU_lines']->getTabName()." as l";
        $this->query.=" WHERE l.ID='".$arr['ID']."'";
        $this->query.=" ORDER BY l.riga";

        return true;
    }

    function newLink($arr) {
        //crea il nuovo TODO ed il link all'applicazione
        //TRANSACTION

        $todo=array(
            "ID"=>'###@id',
            "riga"=>1,
            "testo"=>$arr['testo'],
            "d_creazione"=>date('Ymd:H:i')
        );

        $this->query="DECLARE @id INT;";

        $this->query.="SELECT @id=(SELECT isnull(max(ID)+1,1) FROM ".$this->tabelle['DUDU_lines']->getTabName().");";

        $this->doInsert('DUDU_lines',$todo,'','query');

        $link=array(
            "app"=>"ermes",
            "rif"=>$arr['rif'],
            "dudu"=>'###@id',
        );

        $this->doInsert('DUDU_link',$link,'','query');

        $this->arrQuery[]=$this->query;

        return true;
    }

    function insertLine($arr) {

        $line=array(
            "ID"=>$arr['ID'],
            "riga"=>"###(SELECT isnull(max(riga)+1,1) FROM ".$this->tabelle['DUDU_lines']->getTabName()." WHERE ID='".$arr['ID']."')",
            "testo"=>$arr['testo'],
            "d_creazione"=>date('Ymd:H:i')
        );

        $this->doInsert('DUDU_lines',$line,'','query');

        return true;
    }

} 


?>