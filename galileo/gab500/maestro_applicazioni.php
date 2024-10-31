<?php

include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_app.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_app_livello.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_reparti.php');
include_once(DROOT.'/nebula/galileo/gab500/tabs/MAESTRO_gruppi.php');

class galileoMaestroApp extends galileoOps {

    function __construct() {

        $this->tabelle['MAESTRO_app']=new maestro_app();
        $this->tabelle['MAESTRO_app_livello']=new maestro_app_livello();
        $this->tabelle['MAESTRO_reparti']=new maestro_reparti();
        $this->tabelle['MAESTRO_gruppi']=new maestro_gruppi();
    }

    function getConfigAppUtente($conf) {
        /*
        "generale":{
            "TDD":{
                "TDD":{"ID_coll":1,"nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"TDD","des_reparto":"Team di Direzione","macroreparto":"D","des_macroreparto":"Direzione","ID_gruppo":32,"gruppo":"TDD","des_gruppo":"Direttivo","pos_gruppo":1,"macrogruppo":"","des_macrogruppo":"","pos_macrogruppo":""}
            }
             "VWS":{
                "RS":{"ID_coll":1,"nome":"Matteo","cognome":"Cecconi","concerto":"m.cecconi","reparto":"VWS","des_reparto":"Service Volkswagen","macroreparto":"S","des_macroreparto":"Service","ID_gruppo":1,"gruppo":"RS","des_gruppo":"Resp. Service","pos_gruppo":5,"macrogruppo":"RSC","des_macrogruppo":"Responsabili Officina ","pos_macrogruppo":1}
            }
        }
        */

        $opz=array();

        $coll="";
        $macrogru=array();
        $macrorep=array();

        foreach ($conf as $reparto=>$r) {

            $opz[]="app.tag='".$reparto."' AND app.livello='reparto'";

            foreach ($r as $a) {

                $opz[]="app.tag='".$a["ID_gruppo"]."' AND app.livello='gruppo'";

                $coll=$a["ID_coll"];

                if ($a["macrogruppo"]!="") {
                    $macrogru[$a["macrogruppo"]]=1;
                }

                $macrorep[$a["macroreparto"]]=1;

            }
        }

        foreach ($macrogru as $k=>$v) {
            $opz[]="app.tag='".$k."' AND app.livello='macrogru'";
        }
        foreach ($macrorep as $k=>$v) {
            $opz[]="app.tag='".$k."' AND app.livello='macrorep'";
        }

        $opz[]="app.tag='".$coll."' AND app.livello='coll'";

        ///////////////////////////////////////////////////////////////////////

        $str="";
        foreach ($opz as $o) {
            $str.=" (".$o.") OR";
        }

        $this->query="SELECT ";

        $this->query.="app.livello,
                    app.tag,
                    isnull(app.galassia,'') AS galassia,
                    isnull(app.sistema,'') AS sistema,
                    isnull(app.funzione,'') AS funzione,
                    app.modificatore,
                    liv.pos     
        ";

        $this->query.="FROM ".$this->tabelle['MAESTRO_app']->getTabName()." AS app ";
        $this->query.="INNER JOIN ".$this->tabelle['MAESTRO_app_livello']->getTabName()." AS liv ON app.livello=liv.tag ";

        $this->query.="WHERE app.stato='1' AND ( ".substr($str,0,-3)." ) ";

        $this->query.="ORDER BY liv.pos";
    }

    function getPassiReparto($reparto) {
        //restituisce tutte le autorizzazioni che coinvolgono un determinato reparto (eccetto quelle legate ad un singolo collaboratore)
        $this->query="SELECT
                app.*,
                isnull(app.funzione,'') AS funzione,
                gru.descrizione as des_gruppo,
                isnull(rep.tag,'') AS reparto,
                isnull(mrep.tipo,'') AS tipo,
                isnull(gru.ID,'') AS ID_gruppo,
                isnull(mgru.macrogruppo,'') AS macrogruppo
        ";

        $this->query.=" FROM ".$this->tabelle['MAESTRO_app']->getTabName()." AS app";

        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS rep ON rep.tag='".$reparto."' AND (app.livello='reparto' AND app.tag=rep.tag)";

        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_reparti']->getTabName()." AS mrep ON mrep.tag='".$reparto."' AND (app.livello='macrorep' AND app.tag=mrep.tipo)";

        $this->query.=" LEFT JOIN ".$this->tabelle['MAESTRO_gruppi']->getTabName()." AS gru ON gru.reparto='".$reparto."' AND (app.livello='gruppo' AND cast(app.tag AS VARCHAR(max))=cast(gru.ID AS VARCHAR(max)))";

        $this->query.=" LEFT JOIN (
                    SELECT
                    a.macrogruppo
                    from MAESTRO_gruppi as a
                    WHERE a.reparto='".$reparto."'
                    group by a.macrogruppo
                ) as mgru ON (app.livello='macrogru' AND app.tag=mgru.macrogruppo)
        ";

        $this->query.=" where rep.tag IS NOT NULL OR mrep.tipo IS NOT NULL OR gru.ID IS NOT NULL OR mgru.macrogruppo IS NOT NULL";

    }

} 


?>