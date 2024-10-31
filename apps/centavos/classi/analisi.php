<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once("struttura.php");

class centavosAnalisi {

    //array dei campi del record del PIANO
    protected $piano=array();
    protected $IDperiodo="";

    protected $periodi=array();
    protected $collaboratori=array();

    protected $linkVarDivo=array();

    protected $struttura=false;
    protected $galileo;

    function __construct($piano,$galileo) {

        $this->piano=$piano;
        $this->galileo=$galileo;

        //recupera i periodi all'interno dell'intervallo di validità del piano

        //TEST
        //in realtà al momento del TEST il panorama 1 non è confermato quindi non sarebbe possibile aprire dei periodi su di esso
        /*$this->periodi=array(
            "1"=>array(
                "ID"=>1,
                "piano"=>1,
                "d_inizio"=>"20210701",
                "d_fine"=>"20210930",
                "stato"=>"actual"
            )
        );
        //END TEST*/

        $wclause="piano='".$this->piano['ID']."'";
        $order="d_inizio DESC";

        $this->galileo->executeSelect('centavos','CENTAVOS_periodi',$wclause,$order);
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {
                $this->periodi[$row['ID']]=$row;
            }
        }
                
    }

    function buildPeriodo($periodo) {

        $this->IDperiodo=$periodo;

        $this->piano['data_i']=$this->periodi[$periodo]['d_inizio'];
        $this->piano['data_f']=$this->periodi[$periodo]['d_fine'];
        $this->piano['periodoAnalisi']=$periodo;

        $this->struttura=new centaStruttura($this->piano,$this->galileo);

        $a=array(
            "reparti"=>"'".$this->piano['reparto']."'",
            "data_i"=>$this->piano['data_i'],
            "data_f"=>$this->piano['data_f'],
            "piano"=>$this->piano['ID']
        );

        $this->galileo->executeGeneric('centavos','getCollaboratori',$a,"");
        $result=$this->galileo->getResult();
        if ($result) {
            $fetID=$this->galileo->preFetch('centavos');
            while ($row=$this->galileo->getFetch('centavos',$fetID)) {

                $row['nome']=iconv("ISO-8859-1", "UTF-8", $row['nome']);
                $row['cognome']=iconv("ISO-8859-1", "UTF-8", $row['cognome']);

                if ($row['ID_link']!=0) {
                    //se il link interseca il periodo di analisi
                    //SE PER ERRORE CI FOSSERO PIÙ LINK VALIDI L'ULTIMO SARA' QUELLO VALIDO
                    if ($row['dlink_i']<=$this->periodi[$periodo]['d_fine'] && $row['dlink_f']>=$this->periodi[$periodo]['d_inizio']) {
                        $this->collaboratori[$row['variante']][$row['ID_coll']]=$row;
                        if (!$this->collaboratori[$row['variante']][$row['ID_coll']]['grado']=json_decode($this->collaboratori[$row['variante']][$row['ID_coll']]['grado'],true)) $this->collaboratori[$row['variante']][$row['ID_coll']]['grado']=array();
                    }
                }
            }
        }
    }

    function draw($logged) {

        $logged=(!$logged)?'':$logged;

        foreach ($this->periodi as $k=>$p) {

            if ($logged!='' && ($p['stato']=='new' || $p['hidden']==1 ) ) continue;

            echo '<div id="ctv_analisi_periodo_info_'.$k.'" style="position:relative;">';

                echo '<div style="position:relative;" >';

                    echo '<div style="position:relative;width:80%;margin-top:8px;margin-bottom:8px;padding:2px;box-sizing:border-box;height:20px;cursor:pointer;border-radius:4px;display:inline-block;vertical-align:top;';

                        if ($p['hidden']==1) echo 'border:2px solid red;';
                        else echo 'border:1px solid black;';

                        if ($p['stato']=='actual') echo 'background-color:#a6f7a6;';
                        else if ($p['stato']=='new') echo 'background-color:#ece399;';
                        else if ($p['stato']=='freezed') echo 'background-color:#a6f7ee;';
                        else echo 'background-color:#cccccc;';

                    echo '" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].selectPeriodo(\''.$p['ID'].'\',\''.$logged.'\');" >';

                        echo '<div style="position:relative;display:inline-block;width:45%;height:100%;line-height:100%;text-align:center;" >'.mainFunc::gab_todata($p["d_inizio"]).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:10%;height:100%;line-height:100%;text-align:center;" >';
                            echo '<img style="width:90%;height:70%;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                        echo '</div>';
                        echo '<div style="position:relative;display:inline-block;width:45%;height:100%;line-height:100%;text-align:center;" >'.mainFunc::gab_todata($p["d_fine"]).'</div>';

                    echo '</div>';

                    if ($logged=='') {
                        echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';
                            echo '<img style="width:15px;height:15px;margin-top:11px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/edit.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].ctvAnalisiInfoOpen(\''.$k.'\');"/>';
                        echo '</div>';
                    }

                echo '</div>';

            echo '</div>';

        }

        echo '<script type="text/javascript" >';
            echo 'var obj='.json_encode($this->periodi).';';
            echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadAnalisi(obj);';
        echo '</script>';

    }

    function drawPeriodo($logged) {

        if (!$this->struttura) die ("Errore struttura");

        $p=$this->periodi[$this->IDperiodo];

        $divo=new Divo('centavos_analisi','6%;','80%',0);
        $divo->setBk('#fdbea7');

        $css=array(
            "font-size"=>"1.1em",
            "font-weight"=>"bold",
            "margin-top"=>"4px",
            "margin-left"=>"15px"
        );

        echo '<div id="centavos2canvas" style="width:100%;height:100%;" >';

            echo '<div style="position:relative;font-size:1.3em;font-weight:bold;">';
                echo '<div style="width:90%;';
                    if ($p['stato']=='actual') echo 'background-color:#a6f7a6;';
                    else if ($p['stato']=='new') echo 'background-color:#ece399;';
                    else if ($p['stato']=='freezed') echo 'background-color:#a6f7ee;';
                    else echo 'background-color:#cccccc;';
                echo '">'.$this->piano['reparto'].' - '.$this->piano['descrizione'].' ('.mainFunc::gab_todata($p["d_inizio"]).' - '.mainFunc::gab_todata($p["d_fine"]).')';
                    if ($p['stato']=='close') echo '<img style="position:relative;margin-left:20px;width:20px;top:2px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/freeze.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].freezeAnalisi(\''.$p['ID'].'\');" />';
                echo '</div>';

                echo '<img style="position:absolute;right:40px;top:0px;width:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/print.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].printAnalisi();" />';

                echo '<img id="centavosComprimiImg" style="position:absolute;right:10px;top:0px;width:20px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/comprimi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].comprimiAnalisi();" />';
                echo '<img id="centavosEspandiImg" style="position:absolute;right:10px;top:0px;width:20px;cursor:pointer;display:none;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/centavos/img/espandi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].espandiAnalisi();" />';
            echo '</div>';

            //if ($p['stato']=='freezed') $this->drawFreezed($p,$divo,$css,$logged);

            //else {

                $countDivo=0;

                foreach ($this->collaboratori as $variante=>$v) {

                    $check=false;

                    //se siamo nella visualizzazione personale verifica se il collaboratore appartiene a questa variante
                    if ($logged!="") {
                        foreach ($v as $ID_coll=>$c) {
                            if ($logged==$ID_coll) $check=true;
                        }
                    }
                    else $check=true;

                    if (!$check) continue;

                    if ($p['stato']!='freezed') {
                        //non deve scrivere niente ma definire le sezioni
                        ob_start();
                            $this->struttura->drawSelectVar($variante);
                            $this->struttura->drawStructBody($variante,'analisi');
                        ob_clean();
                    }

                    $txt="";
                    
                    $txt.='<div class="centavosAnalisiMainVarianteAll" style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;">';

                        foreach ($v as $ID_coll=>$c) {

                            if ($logged!="" && $logged!=$ID_coll) continue;


                            if ($p['stato']=='freezed') {
                                $txt.=$this->drawFreezed($p['ID'],$ID_coll);
                            }
                            else {
                                $txt.=$this->drawAnalisiCollaboratore($c,$variante);
                            }
                        }

                    $txt.='</div>';

                    $divo->add_div($variante,'black',0,0,$txt,0,$css);

                    $this->linkVarDivo[$variante]=$countDivo;
                    $countDivo++;

                }

                unset($txt);

                echo '<div style="margin-top:1%;height:94%;">';

                    $divo->build();

                    $divo->draw();

                    /*echo '<div>';
                        echo json_encode($this->galileo->getLog('query'));
                    echo '</div>';*/

                echo '</div>';

                if ($p['stato']!='freezed') {

                    echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/html2canvas/html2canvas.min.js" ></script>';

                    echo '<script type="text/javascript" >';
                        echo 'var obj='.json_encode($this->linkVarDivo).';';
                        echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadVarDivo(obj);';
                    echo '</script>';
                }
            //}

        echo '</div>';
       
    }

    function drawFreezed($periodo,$coll) {

        $txt="";

        $this->galileo->executeSelect('centavos','CENTAVOS_freezed',"periodo='".$periodo."' AND collaboratore='".$coll."'",'');

        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('centavos');

            while ($row=$this->galileo->getFetch('centavos',$fid)) {

                $txt.='<img style="width:97%;" src="'.$row['html'].'" />';
            }
        }

        return $txt;

    }

    /*function drawFreezed($p,$divo,$css,$logged) {

        $this->galileo->executeSelect('centavos','CENTAVOS_freezed',"periodo='".$p['ID']."'",'');

        if ($result=$this->galileo->getResult()) {

            $fid=$this->galileo->preFetch('centavos');

            while ($row=$this->galileo->getFetch('centavos',$fid)) {
                
                if ($temp=json_decode(base64_decode($row['html']),true)) {
                    foreach ($temp as $variante=>$v) {

                        $txt="";
                        foreach ($v as $coll=>$c) {

                            if ($logged!="" && $logged!=$coll) continue;

                            $txt.=$c;

                        }

                        if ($txt=="") continue;

                        $divo->add_div($variante,'black',0,0,'<div class="centavosAnalisiMainVarianteAll" style="width:100%;height:92%;overflow:scroll;overflow-x:hidden;">'.$txt.'</div>',0,$css);
                    }
                }
            }
        }

        unset($temp);

        echo '<div style="margin-top:1%;height:94%;">';

            $divo->build();

            $divo->draw();

        echo '</div>';

    }*/

    function drawAnalisiCollaboratore($coll,$variante) {

        $body=$this->struttura->drawAnalisiColl($coll);

        //$res='<page>';

        $res='<div id="centavosAnalisiMainVariante_'.$variante.'_'.$coll['ID_coll'].'" class="centavosAnalisiMainVariante" data-variante="'.$variante.'" data-coll="'.$coll['ID_coll'].'" style="position:relative;width:98%;margin-top:2px;margin-bottom:2px;border:1px solid black;padding:3px;box-sizing:border-box;">';

            $res.='<div style="font-weight:bold;font-size:1.2em;" >';
                $res.='<div style="position:relative;display:inline-block;vertical-align:bottom;" >('.$coll['ID_coll'].') '.$coll['cognome'].' '.$coll['nome'].'</div>';
                $txt='('.$coll['ID_coll'].') '.$coll['cognome'].' '.$coll['nome'].': ';
                //{"ID_coll":9,"data_i":"20190301","data_f":"21001231","ID_gruppo":4,"gruppo":"TEC","des_gruppo":"Tecnico","posizione":1,"macrogruppo":"TES","des_macrogruppo":"Tecnici Service","posizione_macrogruppo":3,"reparto":"VWS","macroreparto":"S","des_reparto":"Service Volkswagen","rep_concerto":"PV","des_macroreparto":"Service","nome":"Elia","cognome":"Amadori","concerto":"e.amadori","cod_operaio":"18","tel_interno":"","IDDIP":"51","IDMAT":"125","variante":"TEC","ID_link":1,"dlink_i":"20210701","dlink_f":"20211231","grado":{"1":3,"2":3,"3":3}}
                $res.='<div style="position:relative;display:inline-block;width:20%;vertical-align:top;">';
                    $res.='<div id="ctv_simula_punteggio_modulo_1" style="position:relative;box-sizing:border-box;border:2px solid #bf5c15;width:90%;height:30px;margin-left:5%;background-color:#f1bd9188;text-align:center;line-height:30px;font-weight:bold;font-size:1 em;">';
                        $temp=number_format($this->struttura->getTotaleIncentivo(),2,'.','');
                        $res.=$temp.' €';
                        $txt.=$temp;
                    $res.='</div>';
                $res.='</div>';
            $res.='</div>';

            //$res.='<div id="centavosAnalisiMainColl_'.$coll['ID_coll'].'" data-head="'.base64_encode($txt).'" data-body="'.base64_encode($this->struttura->getPrintAnalisiBody()).'" >';
            $res.='<div id="centavosAnalisiMainColl_'.$variante.'_'.$coll['ID_coll'].'" data-variante="'.$variante.'" data-coll="'.$coll['ID_coll'].'">';
                $res.=$body;
                unset($body);
            $res.='</div>';

        $res.='</div>';

        //$res.='</page>';

        return $res;
    }

}
?>