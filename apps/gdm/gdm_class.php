<?php
require_once(DROOT.'/nebula/core/divo/divo.php');
require_once(DROOT.'/nebula/core/veicolo/classi/veicolo_main.php');
require_once(DROOT.'/nebula/core/veicolo/classi/wormhole.php');
require_once(DROOT.'/nebula/apps/gdm/classi/gestione.php');
require_once(DROOT.'/nebula/apps/gdm/classi/richiesta.php');
require_once(DROOT.'/nebula/apps/gdm/classi/richiesta.php');
require_once(DROOT.'/nebula/apps/lizard/classi/lizattach.php');


class gdmApp extends appBaseClass {

    protected $actualVei=false;
    protected $actualLink=false;
    protected $actualRichiesta=false;

    protected $listaTelai=array();

    protected $colori=['#dedcc4','#adc5ba'];

    protected $wh=false;

    function __construct($param,$galileo) {
        
        parent::__construct($galileo);

        $this->loc='/nebula/apps/gdm/';

        $this->param['gdm_tt']="";
        $this->param['gdm_dmstt']="";
        $this->param['gdm_telaio']="";
        $this->param['gdm_dms']="";
        $this->param['gdm_pratica']="";
        $this->param['gdm_ambito']="";
        $this->param['gdm_divo']="";

        $this->loadParams($param);

        if ($this->param['gdm_divo']=="") $this->param['gdm_divo']=0;
        $this->param['gdm_divo']=(int)$this->param['gdm_divo'];

        $this->wh=new veicoloWH('',$this->galileo);

        //se il telaio è già stato identificato
        if ($this->param['gdm_telaio']!="" && $this->param['gdm_dms']!="") {
            $temp=new nebulaVeicolo($this->param['gdm_dms'],$this->galileo);
            $temp->loadTT(array('telaio'=>$this->param['gdm_telaio'],'targa'=>''),1);

            foreach ($temp->getLinks() as $k=>$l) {
                //in infinity il primo è quello legato al veicolo
                //in concerto il primo è l'ultimo abbinamento
                //if ($l['progressivo']==1) {
                    $this->actualLink=$l;
                    break;
                //}
            }

            $this->actualVei=$temp->getInfo();
            //die(json_encode($this->actualVei));
            unset($temp);
        }

        //altrimenti cercalo nei due dms ed unifica le liste attraverso il telaio
        else if ($this->param['gdm_tt'] && $this->param['gdm_tt']!="") {

            foreach ($this->wh->getDmss() as $k=>$d) {

                if ($this->param['gdm_dmstt']!='tutti' && $this->param['gdm_dmstt']!=$d) continue;

                /*$map=$this->wh->linkerSearch('infinity',$this->param['gdm_tt']);

                if ($map['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                    while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                        
                        if ($row['telaio']=='') continue;
                        if ($row['cod_anagra_util']=='' && $row['cod_anagra_intest']=='' && $row['cod_anagra_locat']=='') continue;

                        if (!array_key_exists($row['telaio'],$this->listaTelai)) $this->listaTelai[$row['telaio']]=array();

                        $this->listaTelai[$row['telaio']][]=$row;
                    }
                }*/

                $map=$this->wh->linkerSearch($d,$this->param['gdm_tt']);

                if ($map['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($map['piattaforma'],$map['result']);

                    while ($row=$this->galileo->getFetchPiattaforma($map['piattaforma'],$fid)) {
                        
                        if ($row['telaio']=='') continue;
                        if ($row['cod_anagra_util']=='' && $row['cod_anagra_intest']=='' && $row['cod_anagra_locat']=='') continue;

                        if (!array_key_exists($row['telaio'],$this->listaTelai)) {
                            $this->listaTelai[$row['telaio']]=array();
                            $this->listaTelai[$row['telaio']][]=$row;
                        }

                        else {
                            $i=true;
                            foreach ($this->listaTelai[$row['telaio']] as $k=>$t) {
                                //if ($row['cod_anagra_util']==$t['cod_anagra_util'] || $row['cod_anagra_intest']==$t['cod_anagra_intest'] || $row['cod_anagra_util']==$t['cod_anagra_intest']) {
                                //if ($row['cod_anagra_util']==$t['cod_anagra_util'] || ( $row['cod_anagra_intest']==$t['cod_anagra_intest'] && $row['cod_anagra_util']==$t['cod_anagra_util']) ) {
                                if ($row['cod_anagra_intest']==$t['cod_anagra_intest'] && $row['cod_anagra_util']==$t['cod_anagra_util']) {
                                    $i=false;
                                    break;
                                }
                            }
                            if ($i) $this->listaTelai[$row['telaio']][]=$row;
                        }
                    }
                }
            }

        }
    }

    function initClass() {
        return ' gdmCode(\''.$this->param['nebulaFunzione']['nome'].'\');';
    }

    static function initJS() {
        echo '<script type="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/core/gdm.js?v='.time().'"></script>';
        echo '<script type="text/javascript">';
            echo 'window._nebulaGdm=new nebulaGdm();';
        echo '</script>';
    }

    function customDraw() {

        //echo json_encode($this->param);

        $this->initJS();

        echo '<div style="position:relative;width:100%;height:8%;">';
            $this->drawHead(true);
        echo '</div>';

        echo '<div style="position:relative;width:100%;height:92%;">';
            if ($this->param['gdm_ambito']=="gdmp") $this->drawListaPrelievo();
            elseif ($this->param['gdm_ambito']=="gdms") $this->drawListaStoccaggio();
            elseif ($this->param['gdm_ambito']=="gdmr") $this->drawListaOfficina();
            else $this->drawBody();
        echo '</div>';

        echo '<script type="text/javascript">';

            ob_start();
                include (DROOT.'/nebula/apps/gdm/core/default.js');
            ob_end_flush();

            //window._nebulaApp_storico -> storicoCode

            if ($this->actualVei) {
                $this->actualVei['nomeCliente']=($this->actualLink['des_util']!="")?$this->actualLink['des_util']:$this->actualLink['des_intest'];
                $this->actualVei['nomeCliente'] = mb_convert_encoding( $this->actualVei['nomeCliente'], 'UTF-8', 'UTF-8');
                echo 'var temp='.json_encode($this->actualVei).';';
                echo 'window._nebulaGdm.loadVei(temp);';
            }
            
        echo '</script>';

        //echo $this->actualVei['nomeCliente'];

    }

    function drawHead($icon) {

        echo '<div style="position:relative;width:100%;height:100%;padding:3px;box-sizing:border-box;background-color:#eeeeee;border:1px solid black;border-radius:5px;" >';

            if (!$this->actualVei) {
                echo '<div style="color:red;font-weight:bold;" >Veicolo non identificato</div>';
            }

            else {

                echo '<div style="position:relative;display:inline-block;width:8%;vertical-align:top;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Targa</div>';
                    echo '<div style="font-size:1em;">'.$this->actualVei['targa'].'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:17%;vertical-align:top;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Telaio<span style="margin-left:5px;font-weight:normal;font-size:0.9em;">('.$this->actualVei['dms'].' - '.$this->actualVei['rif'].')</span></div>';
                    echo '<div style="font-size:1em;">'.$this->actualVei['telaio'].'</div>';
                echo '</div>';

                echo '<div style="position:relative;display:inline-block;width:17%;vertical-align:top;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Marca</div>';
                    echo '<div style="font-size:1em;">'.substr($this->actualVei['cod_marca'].' - '.$this->actualVei['des_marca'],0,18).'</div>';
                echo '</div>';

                if ($this->actualLink) {

                    echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;" >';
                        echo '<div style="font-weight:bold;font-size:0.9em;">Intestatario</div>';
                        echo '<div id="gdmLinkIntest" data-txt="'.$this->actualLink['des_intest'].'" style="font-size:1em;">'.substr($this->actualLink['des_intest'],0,30).'</div>';
                    echo '</div>';

                    echo '<div style="position:relative;display:inline-block;width:25%;vertical-align:top;" >';
                        echo '<div style="font-weight:bold;font-size:0.9em;">Utilizzatore</div>';
                        echo '<div id="gdmLinkUtil" data-txt="'.$this->actualLink['des_util'].'" style="font-size:1em;">'.substr($this->actualLink['des_util'],0,30).'</div>';
                    echo '</div>';

                    if ($icon) {
                        echo '<div style="position:relative;display:inline-block;width:7%;vertical-align:top;text-align:right;" >';
                            echo '<img style="width:30px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].unsetVeicolo();"/>';
                        echo '</div>';
                    }
                }

                else {
                    if ($icon) {
                        echo '<div style="position:relative;display:inline-block;width:57%;vertical-align:top;text-align:right;" >';
                            echo '<img style="width:30px;height:30px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/gdm/img/chiudi.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].unsetVeicolo();"/>';
                        echo '</div>';
                    }
                }

            }

        echo '</div>';

    }

    function drawBody() {

        $divo=new Divo('gdm','5%','94%',true);

        $divo->setBk('#a8ceb7');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        /*$css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );*/

        //$divo->setChkimgCss($css2);

        ////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:100%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';

            ob_start();

            if (!$this->actualVei) {
                if ($this->param['gdm_tt'] && $this->param['gdm_tt']!="") $this->drawListaTelai();
                else $this->drawQuery();
            }
            else $this->drawGestione();

            $divo->add_div('Gestione','black',0,"",ob_get_clean(),($this->param['gdm_divo']==0?1:0),$css);

            /*ob_start();

            $this->drawListaRichiesteAperte();

            $divo->add_div('Richieste','black',0,"",ob_get_clean(),($this->param['gdm_divo']==1?1:0),$css);
            */

            $divo->build();

            $divo->draw();

        echo '</div>';
            
    }

    function drawGestione() {

        $gestione=new gdmGestione($this->actualVei['telaio'],$this->galileo);
        $gestione->draw();
        
    }

    function drawGestioneSolo() {

        $this->initJS();

        echo '<div style="position:relative;width:100%;height:8%;">';
            $this->drawHead(false);
        echo '</div>';

        echo '<div style="position:relative;width:100%;height:92%;">';
            $this->drawGestione();
        echo '</div>';

        echo '<script type="text/javascript" >';

            if ($this->actualVei) {
                $this->actualVei['nomeCliente']=($this->actualLink['des_util']!="")?$this->actualLink['des_util']:$this->actualLink['des_intest'];
                $this->actualVei['nomeCliente'] = mb_convert_encoding( $this->actualVei['nomeCliente'], 'UTF-8', 'UTF-8');
                echo 'var temp='.json_encode($this->actualVei).';';
                echo 'window._nebulaGdm.loadVei(temp);';
            }

            echo 'window._nebulaGdm.app="odl";';
            
        echo '</script>';
    }

    function drawListaTelai() {

        //echo '<div>'.json_encode($this->wh->getLog()).'</div>';
        //echo '<div>'.json_encode($this->listaTelai).'</div>';

        echo '<div style="position:relative;width:100%;height:100%;overflow:scroll;overflow-x:hidden;" >';
            
            $col=1;

            foreach ($this->listaTelai as $telaio=>$lista) {

                $col=($col==1)?0:1;

                foreach ($lista as $k=>$l) {

                    echo '<div style="position:relative;width:97%;margin-top:8px;margin-bottom:8px;padding:5px;box-sizing:border-box;border:1px solid black;border-radius:6px;box-shadow: 3px 3px #bbbbbb;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicolo(\''.$l['dms'].'\',\''.$l['telaio'].'\');" >';

                        echo '<div style="position:relative;display:inline-block;width:2%;">('.substr($l['dms'],0,1).')</div>';
                        echo '<div style="position:relative;display:inline-block;width:18%;font-weight:bold;">'.$l['telaio'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:9%;font-weight:bold;">'.$l['targa'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:8%;">'.$l['modello'].'</div>';
                        echo '<div style="position:relative;display:inline-block;width:15%;font-size:0.9em;">'.substr($l['des_veicolo'],0,20).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:24%;font-weight:bold;"><span style="font-size:0.8em;font-weight:normal;">Util:&nbsp;</span>'.strtolower(substr($l['ragsoc_util'],0,27)).'</div>';
                        echo '<div style="position:relative;display:inline-block;width:24%;font-weight:bold;"><span style="font-size:0.8em;font-weight:normal;">Inte:&nbsp;</span>'.strtolower(substr($l['ragsoc_intest'],0,27)).'</div>';

                    echo '</div>';
                }
            }

            //echo '<div>'.json_encode($this->wh->getLog()).'</div>';

        echo '</div>';
    }

    function drawListaRichiesteAperte() {

        echo '<div id="gdm_gestione_richieste" style="width:100%;height:100%;" >';
            echo '<div style="position:relative;width:100%;margin-top:20px;text-align:center;" >';
                echo '<img style="width:50px;height:50px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/busy.gif" />';
            echo '</div>';
        echo '</div>';

       /* $richiesta=false;

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','gdm');

        $this->galileo->executeSelect('gdm','GDM_richieste',"statoRi='Aperta'",'dataRi DESC');
        $result=$this->galileo->getResult();

        $col=1;

        if ($result) {
            $fid=$this->galileo->preFetch('gdm');

            echo '<div style="position:relative;display:inline-block;width:47%;height:100%;overflow:scroll;overflow-x:hidden;" >';

                while($row=$this->galileo->getFetch('gdm',$fid)) {

                    //non scrivere la richiesta relativa al telaio attivo (perché viene scritta espansa a lato)
                    if ($this->actualVei && $row['idTelaio']==$this->actualVei['telaio']) {
                        $this->actualRichiesta=new gdmRichiesta($this->param['gdm_ambito'],$row,$this->galileo);
                        continue;
                    }

                    $col=($col==1)?0:1;

                    echo '<div style="position:relative;width:85%;border:1px solid black;margin-top:5px;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$row['idTelaio'].'\',\''.$row['dms'].'\');">'; 
                    //echo '<div style="position:relative;width:75%;border:1px solid black;margin-top:5px;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$row['idTelaio'].'\',\'infinity\');">'; 
                        $richiesta=new gdmRichiesta($this->param['gdm_ambito'],$row,$this->galileo);
                        $richiesta->draw(false);
                    echo '</div>';
                }

            echo '</div>';  
        }

        echo '<div style="position:relative;display:inline-block;width:50%;height:99%;margin-left:3%;" >';

            imageSelect::imageSelectInit();

            if ($this->actualRichiesta) {
                $this->actualRichiesta->drawForm();
            }

        echo '</div>';*/


    }

    function drawListaOfficina() {

        $divo=new Divo('gdmr','5%','94%',true);

        $divo->setBk('#a8ceb7');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        ////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:100%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';

            ob_start();

            $this->drawListaRichiesteAperte();

            $divo->add_div('Richieste','black',0,"",ob_get_clean(),1,$css);

            ob_start();

            $this->drawExcalibur();

            $divo->add_div('Lista Richieste','black',0,"",ob_get_clean(),0,$css);

            $divo->build();

            $divo->draw();

        echo '</div>';

        echo '<script type="text/javascript" >';
            echo 'window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].gestioneRichieste(\''.($this->actualVei?$this->actualVei['telaio']:'').'\',\''.$this->param['gdm_ambito'].'\');';
        echo '</script>';
    }

    function drawListaPrelievo() {
        //echo $this->param['gdm_ambito'];

        $arr=array(
            "statoOp"=>"Richiesto",
            "ambito"=>"prelievo"
        );

        $divo=new Divo('gdmp','5%','94%',true);

        $divo->setBk('#a8ceb7');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        ////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:100%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';

            ob_start();

            $this->drawListaPreSto($arr);

            $divo->add_div('Richieste di Prelievo','black',0,"",ob_get_clean(),0,$css);

            ob_start();

                $liz=new lizardAttach('gdmP',$this->galileo);
                $liz->draw();

            $divo->add_div('Lista Prelievo','black',0,"",ob_get_clean(),0,$css);

            $divo->build();

            $divo->draw();

        echo '</div>';
    }

    function drawListaStoccaggio() {
        //echo $this->param['gdm_ambito'];

        $arr=array(
            "statoOp"=>"Pronto per stoccaggio",
            "ambito"=>"stoccaggio"
        );

        $divo=new Divo('gdms','5%','94%',true);

        $divo->setBk('#a8ceb7');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.3em",
            "margin-left"=>"15px",
            "margin-top"=>"2px"
        );

        ////////////////////////////////////////////////////////

        echo '<div style="position:relative;display:inline-block;width:100%;height:100%;padding:3px;box-sizing:border-box;vertical-align:top;">';

            ob_start();

            $this->drawListaPreSto($arr);

            $divo->add_div('Richieste di Stoccaggio','black',0,"",ob_get_clean(),0,$css);

            ob_start();

                $liz=new lizardAttach('gdmS',$this->galileo);
                $liz->draw();

            $divo->add_div('Lista Stoccaggio','black',0,"",ob_get_clean(),0,$css);

            $divo->build();

            $divo->draw();

        echo '</div>';
    }

    function drawListaPreSto($arr) {
        
        $richiesta=false;
        $actualRichiesta=false;

        $colori=['#dedcc4','#adc5ba'];
        $col=1;

        echo '<div style="position:relative;display:inline-block;width:47%;height:100%;overflow:scroll;overflow-x:hidden;vertical-align:top;" >';

            $this->galileo->executeGeneric("gdm","getOperazioniPreSto",$arr,'');
            $result=$this->galileo->getResult();

            if ($result) {
                $fid=$this->galileo->preFetch('gdm');

                while($row=$this->galileo->getFetch('gdm',$fid)) {

                    $info=array(
                        "id"=>$row['idRi'],
                        "statoRi"=>$row['statoRi'],
                        "dataRi"=>$row['data_rif'],
                        "idTelaio"=>$row['idTelaio'],
                        "nomeCliente"=>$row['nomeCliente'],
                        "targa"=>$row['targa'],
                        "tipoVeicolo"=>$row['tipoVeicolo'],
                        "numPratica"=>$row['numPratica'],
                        "dms"=>$row['dms']
                    );

                    //non scrivere la richiesta relativa al telaio attivo (perché viene scritta espansa a lato)
                    if ($this->actualVei && $row['idTelaio']==$this->actualVei['telaio']) {
                        $actualRichiesta=new gdmRichiesta($arr['ambito'],$info,$this->galileo);
                        continue;
                    }

                    $col=($col==1)?0:1;

                    echo '<div style="position:relative;width:85%;border:1px solid black;margin-top:5px;background-color:'.$colori[$col].';cursor:pointer;" onclick="window._nebulaGdm.refreshRichiesta(\''.$info['id'].'\',\''.$arr['ambito'].'\');">'; 
                    //echo '<div style="position:relative;width:75%;border:1px solid black;margin-top:5px;background-color:'.$this->colori[$col].';cursor:pointer;" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].setVeicoloRichiesta(\''.$row['idTelaio'].'\',\'infinity\');">'; 
                        $richiesta=new gdmRichiesta($arr['ambito'],$info,$this->galileo);
                        $richiesta->draw(false);
                    echo '</div>';
                }
            }

        echo '</div>';

        echo '<div id="gdm_actual_richiesta_'.$arr['ambito'].'" style="position:relative;display:inline-block;width:50%;height:99%;margin-left:3%;vertical-align:top;" >';

            if ($actualRichiesta) {
                $actualRichiesta->drawForm();
            }

        echo '</div>';

    }

    function drawExcalibur() {

    }

    function drawQuery() {
    }



}
?>