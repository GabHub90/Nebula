<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/ermes/classi/ticket.php');

class ermes {

    protected $lista=array();

    //avendo abilitato il check dei reparti non ha più senso il check dei macroreparti
    protected $checkMrep=array('A','M','S','V');

    //reparti che possono ricevere ticket
    protected $checkRep=array('RIT','FCM','VGM','ACS','AVS','BRC','CAR','PAS','PCS','AUS','POS','UPM','VWS','ACV','AUV','PAV','PCV','POV','SKV','VPM','VWV');

    //reparti che possono ricevere ticket
    protected $checkGruppi=array('BRC','ITR','ITT','MAG','RC','RM','RS','RV','VEN','vRM');

    //oggetto js per l'apertura e la chiusura del ticket
    //protected $contesto='';

    protected $galileo;

    function __construct($galileo) {

        $this->galileo=$galileo;
    }

    /*function setContesto($c) {
        $this->contesto=$c;
    }*/

    function buildRep() {

        $ret=array(
            "reparti"=>array(),
            "macrorep"=>array()
        );

        $this->galileo->getReparti('','trep.sede,trep.tipo,trep.tag');
        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetchBase('reparti');
            while ($row=$this->galileo->getFetchBase('reparti',$fid)) {
                //if (in_array($row['macroreparto'],$this->checkMrep)) {
                    if (in_array($row['reparto'],$this->checkRep)) {    
                        $ret['reparti'][$row['sede']][$row['macroreparto']][$row['reparto']]=$row['descrizione'];
                        $ret['macrorep'][$row['macroreparto']]=$row['des_macroreparto'];
                    }
                //}
            }
        }

        return $ret;
    }

    function buildColl($logged) {
        //////////////////////////////////////////////////

        $ret=array(
            "coll"=>array(),
            "defColl"=>array(
                "sede"=>"",
                "macrorep"=>"",
                "reparto"=>""
            )
        );

        $first=true;

        foreach ($logged as $k=>$r) {

            $c=false;
            //in realtà ci può essere un solo array
            foreach ($r as $gruppo=>$g) {
                if (in_array($k,$this->checkRep)) {
                    if (in_array($gruppo,$this->checkGruppi)) {
                        if ($first) {
                            $ret['defColl']['sede']=$g['sede'];
                            $ret['defColl']['macrorep']=$g['macroreparto'];
                            $ret['defColl']['reparto']=$k;
                            $first=false;
                        }
                        $c=true;
                    }
                }
            }


            if (!$c) continue;
            
            $this->galileo->getCollaboratori("reparto",$k,date('Ymd'));
            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetchBase('maestro');
                while ($row=$this->galileo->getFetchBase('maestro',$fid)) {
                    if ($row['concerto']!='' && in_array($row['gruppo'],$this->checkGruppi)) {
                        $ret['coll'][$row['concerto']]=$row;
                    }
                }
            }
        }

        return $ret;
    }

    function loadPanorama($param,$log,$mono) {

        // {"sede":"PU","mrep":"A","reparto":"RIT"}

        //#####################################
        /*TEST
        $row=array(
            "1"=>array(
                "ID"=>1,
                "categoria"=>'INF',
                "reparto"=>"VWS",
                "des_reparto"=>"Service Volkswagen",
                "creatore"=>"m.cecconi",
                "d_creazione"=>"20230831:11:21",
                "d_chiusura"=>"",
                "mittente"=>'{"dms":"infinity","id_anagrafica":"71530","ragsoc":"cecconi matteo","telefono":"3331323292","mail":"matteo.cecconi@gabellini.it"}',
                "gestore"=>"",
                "urgenza"=>0,
                "stato"=>"attesa",
                "scadenza"=>"",
                "padre"=>"",
                "react"=>"",
                "nota"=>"ticket fittizio di prova",
                "chat"=>1
            ),
            "2"=>array(
                "ID"=>2,
                "categoria"=>'INF',
                "reparto"=>"AUS",
                "des_reparto"=>"Service Audi",
                "creatore"=>"m.cecconi",
                "d_creazione"=>"20230901:17:16",
                "d_chiusura"=>"",
                "mittente"=>'{"dms":"infinity","id_anagrafica":"71530","ragsoc":"cecconi matteo","telefono":"3331323292","mail":"matteo.cecconi@gabellini.it"}',
                "gestore"=>"",
                "urgenza"=>0,
                "stato"=>"attesa",
                "scadenza"=>"",
                "padre"=>"",
                "react"=>"",
                "nota"=>"ticket fittizio di prova",
                "chat"=>2
            ),
            "3"=>array(
                "ID"=>3,
                "categoria"=>'AHW',
                "reparto"=>"RIT",
                "des_reparto"=>"Reparto IT",
                "creatore"=>"m.cecconi",
                "d_creazione"=>"20230911:17:16",
                "d_chiusura"=>"",
                "mittente"=>'{"dms":"","id_anagrafica":"","ragsoc":"m.cecconi","telefono":"3331323292","mail":"matteo.cecconi@gabellini.it"}',
                "gestore"=>"",
                "urgenza"=>0,
                "stato"=>"attesa",
                "scadenza"=>"",
                "padre"=>"",
                "react"=>"",
                "nota"=>"ticket fittizio di prova",
                "chat"=>3
            )
        );*/
        //#####################################
        
        $this->galileo->executeGeneric('ermes','getPanorama',$param,'');

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('ermes');
            while ($row=$this->galileo->getFetch('ermes',$fid)) {
                echo '<div style="width:95%;">';

                    $lista[$row['ID']]=new ermesTicket($this->galileo);
                    $lista[$row['ID']]->setContesto($param['contesto']);
                    $lista[$row['ID']]->build($row);

                    $lista[$row['ID']]->drawLista($log,$mono);

                    echo '<hr style="width:85%;margin-top:10px;margin-bottom:10px;" />';
                
                echo '</div>';
            }
        }

        //echo json_encode($param);
    }

    function loadGestione($param,$log,$mono) {

        $this->galileo->executeGeneric('ermes','getGestione',$param,'');

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('ermes');
            while ($row=$this->galileo->getFetch('ermes',$fid)) {
                echo '<div style="width:95%;">';

                    $lista[$row['ID']]=new ermesTicket($this->galileo);
                    $lista[$row['ID']]->setContesto($param['contesto']);
                    $lista[$row['ID']]->build($row);

                    $lista[$row['ID']]->drawLista($log,$mono);

                    echo '<hr style="width:85%;margin-top:10px;margin-bottom:10px;" />';
                
                echo '</div>';
            }
        }

        //echo json_encode($param);
    }

    function loadMiei($param,$mono) {

        $this->galileo->executeGeneric('ermes','getMiei',$param,'');

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('ermes');
            while ($row=$this->galileo->getFetch('ermes',$fid)) {
                echo '<div style="width:95%;">';

                    $lista[$row['ID']]=new ermesTicket($this->galileo);
                    $lista[$row['ID']]->setContesto($param['contesto']);
                    $lista[$row['ID']]->build($row);

                    $lista[$row['ID']]->drawLista(false,$mono);

                    echo '<hr style="width:85%;margin-top:10px;margin-bottom:10px;" />';
                
                echo '</div>';
            }
        }

        //echo json_encode($param);
    }



}

?>