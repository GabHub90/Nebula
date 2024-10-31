<?php
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/divo/divo.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/odl_func.php");
include_once($_SERVER['DOCUMENT_ROOT']."/nebula/core/odl/pratica_func.php");
require_once("wormhole.php");

class avalonSetday {

    protected $param=array();

    protected $odlFlag=false;

    protected $wh;
    protected $odlFunc;
    protected $galileo;

    function __construct($param,$galileo) {

        $this->param=$param;
        $this->galileo=$galileo;

        //autorizza ad aprire gli ordini cliccandoci sopra
        if (isset($param['odlFlag'])) {
            if ($param['odlFlag']==1) $this->odlFlag=true;
        }

        //blocca e sblocca la checkbox per identificare gli ordini
        if (isset($param['chkFlag'])) {
            $this->param['chkFlag']=$param['chkFlag'];
        }
        else $this->param['chkFlag']=true;

        //solo clienti NON arrivati
        if (isset($param['inarrivoFlag'])) {
            $this->param['inarrivoFlag']=$param['inarrivoFlag'];
        }
        else $this->param['inarrivoFlag']=false;

        $this->odlFunc=new nebulaOdlFunc($this->galileo);

        $this->wh=new avalonWHole($param['reparto'],$this->galileo);

        if (!isset($this->param['officina']) && isset($this->param['inizio'])) {

            $a=array(
                "inizio"=>$this->param['inizio'],
                "fine"=>$this->param['fine']
            );
    
            $this->wh->build($a);

            $this->param['officina']=$this->odlFunc->getDmsRep($this->wh->getTodayDms($this->param['inizio']),$this->param['reparto']);
        }
        
    }

    function getPren() {
        //echo json_encode($this->param);
        $this->wh->getPrenotazioni($this->param['inizio'],$this->param['fine'],$this->param['officina']);
    }

    function draw() {

        nebulaPraticaFunc::initJS();

        echo '<div style="width:100%;height:8%;padding:3px;box-sizing:border-box;font-weight:bold;font-size:1.5em;margin-left:15px;" >';
            $t=mainFunc::gab_tots($this->param['inizio']);
            echo substr(mainFunc::gab_weektotag(date('w',$t)),0,3).' '.mainFunc::gab_todata($this->param['inizio']);
        echo '</div>';

        echo '<div style="width:100%;height:92%;padding:3px;box-sizing:border-box;" >';

            echo '<div style="width:100%;">';

                $divo=new Divo('tempo','5%','95%',true);

                $divo->setBk('#6bc7d6');

                $css=array(
                    "font-weight"=>"bold",
                    "font-size"=>"1.1em",
                    "margin-left"=>"10px",
                    "margin-top"=>"0px"
                );

                $txt='<div style="width:100%;" >';

                    $txt.='<div style="height:10%;" >';
                        $txt.='<img style="position:relative;margin-left:5px;width:40px;height:35px;margin-top:3px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/chime.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openChime(\''.$this->param['reparto'].'\',\'S\',\''.$this->param['inizio'].'\',\''.$this->wh->getTodayDms($this->param['inizio']).'\');" />';
                        $txt.='<img style="position:relative;margin-left:5px;width:50px;height:35px;margin-top:3px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/ringraziamento.png" onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openChime(\''.$this->param['reparto'].'\',\'TG\',\''.$this->param['inizio'].'\',\''.$this->wh->getTodayDms($this->param['inizio']).'\');" />';
                    $txt.='</div>';

                    $txt.='<div id="avalon_right_pren" style="height:90%;" >';

                        $txt.='<div style="text-align:center;">';
                            $txt.='<img style="width:40px;height:40px;margin-top:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/busy.gif" />';
                        $txt.='</div>';

                    $txt.='</div>';

                $txt.='</div>';

                $divo->add_div('Prenotazioni','black',0,"",$txt,1,$css);

                $txt='<div id="avalon_right_lav" style="width:100%;" >';
                    $txt.='<div style="text-align:center;">';
                        $txt.='<img style="width:40px;height:40px;margin-top:20px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/busy.gif" />';
                    $txt.='</div>';
                $txt.='</div>';

                $divo->add_div('Lavorazioni','black',0,"",$txt,0,$css);

                unset($txt);

                $divo->build();

                $divo->draw();

            echo '</div>';

            echo '<script type="text/javascript" >';
                echo 'window["_nebulaApp_"+window._nebulaApp.getTagFunzione()].loadDay('.($this->odlFlag?1:0).');';
            echo '</script>';

        echo '</div>';
    }

    function drawPren() {

        $this->getPren();

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        echo '<div style="position:relative;width:97%;">';

            foreach ($this->wh->exportMap() as $m) {

                if ($m['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                    $txt="";
                    $lam="";
                    $ore=0;
                    $rif="";
                    $count=0;
                    $color="#cccccc";

                    $pratica=false;
                    $nocar=false;

                    /*{"dat_inserimento":"20210826","cod_officina":"PV","cod_officina_prenotazione":"PV","cod_stato_commessa":"RP","cod_movimento":"OOP","rif":"1358521","num_commessa":"0","ind_preventivo":"N",
                    "cod_tipo_trasporto":"NO","num_riga":1,"lam":"A","des_riga":"INFOTAINMENT SI SPEGNE E DA ERRORI ","ind_stato":"L","ind_chiuso":"N","d_pren":"20210908:07:30","d_ricon":"20210909:18:15",
                    "d_entrata":"xxxxxxxx:xx:xx","d_fine":"xxxxxxxx:xx:xx","ore":"1.50","ore_isla":"2.50","subrep":"DIAPV","d_inc":"20210908:12:00","d_fine_inc":"20210909:15:00","d_fix":"xxxxxxxx:xx:xx",
                    "distribuzione":"JMP_125_1","prog_spalm":1,"d_spalm":"20210909:13:30","ore_spalm":"1.50","dat_prenotazione_inc":{"date":"2021-09-08 12:00:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},
                    "dat_prenotazione_det":{"date":"2021-09-09 13:30:00.000000","timezone_type":3,"timezone":"Europe\/Berlin"},"num_rif_veicolo":"5043623","num_rif_veicolo_progressivo":1,
                    "cod_anagra_util":"142264","cod_anagra_intest":"","cod_anagra_loc":"","cod_anagra_fattura":"","cod_accettatore":"m.ghiandoni","mat_targa":"GA111ND","mat_telaio":"TMBEH6NWXL3089545",
                    "cod_veicolo":"NW13C5","des_veicolo":"Scala Sport 1,0 TGI 66 kW 6-Gang mech. G-TEC","util_ragsoc":"MONTANARI DAVIDE","intest_ragsoc":""}*/

                    while ($row=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                        if ($m['dms']=='concerto') {

                            //se non c'è data di entrata e non ci sono lamentati significa che è una prenotazione svuotata
                            if ($row['d_entrata']=='xxxxxxxx:xx:xx' && $row['des_riga']=='') continue;

                            //if ($row['ind_chiuso']=='S') $row['cod_stato_commessa']='CH';
                            //elseif ($row['d_entrata']!='xxxxxxxx:xx:xx') $row['cod_stato_commessa']='AP';

                            //se siamo nel reparto PORSCHE INTERNO di CONCERTO occorre verificare se esiste un CONTRATTO su INFINITY
                            if ($row['cod_officina']=='PI') {

                                $h=$this->wh->getContrattoInfinity($row['mat_telaio']);

                                if ($h['result']) {
                                    $fid2=$this->galileo->preFetchPiattaforma($h['piattaforma'],$h['result']);
                                    while ($row2=$this->galileo->getFetchPiattaforma($h['piattaforma'],$fid2)) {

                                        foreach ($row2 as $k2=>$v2) {
                                            $row[$k2]=$v2;
                                        }
                                    }
                                }

                            }
                        }

                        if ($rif!=$row['rif']) {

                            /*######################################
                            //recupera informazioni PRATICA GAB500
                            //Infinity: 2022-07-15 10:04:57.820 / Concerto: 528808
                            $pratica=new nebulaPraticaFunc($row['pratica'],$m['dms'],'S',$this->odlFunc);
                            $pratica->setDefaultAlert();
                            //$pratica->buildCommessa($row['rif']);
                            //$pratica->buildPratica();
                            //######################################*/

                            //scrittura del blocco
                            if ($lam!="") {

                                    $pratica->richiesteMateriale();
                                    $tempstato=$pratica->getStato('','');
                                
                                    if ($tempstato) $tempColor=$this->odlFunc->getStatoOdl($tempstato,$m['dms']);
                                    if ($tempColor) $color=$tempColor['colore'];

                                    $txt.=$this->drawBlocco($this->tempRow,$m,$color,$count,$pratica);

                                    $txt.='<div style="font-size:1.05em;color:chocolate;font-weight:bold;" ><span style="color:#777777;margin-right:3px;">('.number_format($ore,2,'.','').' ut)</span>'.substr(utf8_encode($lam),0,-3).'</div>';

                                //chiusura del DIV aperto in BLOCCO
                                $txt.='</div>';

                                $txt.='<div id="avalon_odl_block_edit_'.$this->tempRow['rif'].'" style="display:none;">';
                                $txt.='</div>';
                                
                                /////////////////////////////////
                                $ret='<div style="position:relative;margin-top:2px;margin-bottom:2px;padding:2px;border:1px solid black;box-sizing:border-box;" >';
                                    if ($nocar) {
                                        $ret.='<div style="position:absolute;width:100%;height:100%;top:0px;left:0px;z-index:1;opacity:0.2;text-align:center;" >';
                                            $ret.='<img style="position:relative;width:140px;top:50%;transform: translate(0px, -50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/noarrivo.png" />';
                                        $ret.='</div>';
                                    }

                                    //$ret.='<div>'.$this->tempRow['id_nuovo'].' '.$this->tempRow['d_arrivo'].'</div>';

                                    $ret.='<div style="position:relative;z-index:2;background-color:transparent;" >';
                                        $ret.=$txt;
                                    $ret.='</div>';

                                $ret.='</div>';

                                echo $ret;

                                $ret="";
                                $txt="";
                                $lam="";
                            }

                            if ($this->param['inarrivoFlag'] && $row['cod_stato_commessa']=='CH') continue;
                            
                            //if (array_key_exists($row['cod_stato_commessa'],$this->statoOdl[$m['dms']])) $color=$this->statoOdl[$m['dms']][$row['cod_stato_commessa']]['colore'];
                            
                            //legge lo stato in base all'estrazione dal DMS (AP o CH)
                            $tempstato=$this->odlFunc->getStatoOdl($row['cod_stato_commessa'],$m['dms']);

                            if (!$tempstato) continue;
                            if ($this->param['inarrivoFlag'] && $tempstato['codice']=='AP') continue;

                            $row['cod_stato_commessa']=$tempstato['codice'];

                            $count++;

                            //$pratica=false;

                            //######################################
                            //recupera informazioni PRATICA GAB500
                            //Infinity: 2022-07-15 10:04:57.820 / Concerto: 528808
                            $pratica=new nebulaPraticaFunc($row['pratica'],'0',$m['dms'],'S',$this->odlFunc);
                            $pratica->setDefaultAlert();
                            //$pratica->buildCommessa($row['rif']);
                            //$pratica->buildPratica();
                            //######################################

                            $rif=$row['rif'];
                            $lam="";
                            $ore=0;

                            $nocar=false;
                            if (isset($row['id_nuovo'])) {
                                if ($row['id_nuovo']!=0 && $row['d_arrivo']=='') $nocar=true;
                            }
                        }

                        $lam.=ucfirst(strtolower($row['des_riga'])).' - ';
                        $ore+=(float) $row['ore_isla'];

                        $this->tempRow=$row;

                        $pratica->addLam($this->tempRow);

                    }

                    //scrittura dell'ultimo blocco
                    if ($lam!="") {

                            $pratica->richiesteMateriale();
                            $tempstato=$pratica->getStato('','');
                                    
                            if ($tempstato) $tempColor=$this->odlFunc->getStatoOdl($tempstato,$m['dms']);
                            if ($tempColor) $color=$tempColor['colore'];

                            $txt.=$this->drawBlocco($this->tempRow,$m,$color,$count,$pratica);

                            $txt.='<div id="avalon_odl_block_'.$this->tempRow['rif'].'" style="font-size:1.05em;color:chocolate;font-weight:bold;"><span style="color:#777777;margin-right:3px;">('.number_format($ore,2,'.','').' ut)</span>'.substr(utf8_encode($lam),0,-3).'</div>';

                        //chiusura del DIV aperto in BLOCCO
                        $txt.='</div>';

                        $txt.='<div id="avalon_odl_block_edit_'.$this->tempRow['rif'].'" style="display:none;">';
                        $txt.='</div>';

                        /////////////////////////////////

                        $ret='<div style="position:relative;margin-top:2px;margin-bottom:2px;padding:2px;border:1px solid black;box-sizing:border-box;" >';
                            if ($nocar) {
                                $ret.='<div style="position:absolute;width:100%;height:100%;top:0px;left:0px;z-index:1;opacity:0.2;text-align:center;" >';
                                    $ret.='<img style="position:relative;width:140px;top:50%;transform: translate(0px, -50%);" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/noarrivo.png" />';
                                $ret.='</div>';
                            }

                            $ret.='<div style="position:relative;z-index:2;background-color:transparent;" >';
                                $ret.=$txt;
                            $ret.='</div>';
                            
                        $ret.='</div>';

                        echo $ret;
                    }
                }
            }

        echo '</div>';

        //echo '<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        //nebulaPraticaFunc::initJS();
    }

    function drawBlocco($row,$m,$color,$count,$pratica) {

        if ($row['cod_stato_commessa']=='CH') $color='white';
        if ($row['cod_stato_commessa']=='AP') $color='#ffff65';

        $txt='<div style="background-color:'.$color.';">';

            $txt.='<div style="position:relative;display:inline-block;width:13%;vertical-align:top;line-height:20px;font-size:0.9em;">';
                $txt.='<span style="vertical-align:top;">'.$count.' -</span>';

                if ($row['des_riga']!="" && $row['cod_stato_commessa']!='CH' && $this->param['chkFlag']) {
                    $txt.='<input id="avalonPrenCheckbox'.$m['dms'].'_'.$row['rif'].'" type="checkbox" style="margin-left:3px;width:10px;vertical-align:top;" data-dms="'.$m['dms'].'" data-rif="'.$row['rif'].'" />';
                }

            $txt.='</div>';

            $txt.='<div style="position:relative;display:inline-block;width:80%;vertical-align:top;font-weight:bold;line-height:20px;">';

                $txt.=substr(mainFunc::gab_todata(substr($row['d_pren'],0,8)),0,5).' '.substr($row['d_pren'],9,5);

                if ($row['cod_stato_commessa']=='CH') {
                    $txt.='<span style="margin-left:3px;font-size:0.9em;color:red;">CHIUSO</span>';
                }
                elseif ($temptra=$this->odlFunc->getTrasporto($row['cod_tipo_trasporto'],$m['dms'])) {
                    $txt.='<span style="margin-left:3px;font-size:0.8em;">'.substr($temptra['testo'],0,8).'</span>';
                }

                $txt.='<img style="position:relative;margin-left:5px;width:15px;height:10px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/main/img/blackarrowR.png" />';
                $t=mainFunc::gab_tots(substr($row['d_ricon'],0,8));
                $txt.='<span style="margin-left:5px;">'.(substr($row['d_ricon'],0,8)!='xxxxxxxx'?substr(mainFunc::gab_weektotag(date('w',$t)),0,3):'').' '.substr(mainFunc::gab_todata(substr($row['d_ricon'],0,8)),0,5).' '.substr($row['d_ricon'],9,5).'</span>';

            $txt.='</div>';

            $txt.='<div style="position:relative;display:inline-block;width:5%;vertical-align:top;line-height:20px;text-align:center;">';

                $chime=$pratica->getChimeApp($row['rif']);

                if (!$chime) $txt.='<img style="position:relative;width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_G.png" />';
                else {
                    if(isset($chime['stato'])) {
                        //$txt.= '<div>'.$chime['stato'].'</div>';
                        if ($chime['stato']=='enabled') {
                            if (isset($chime['result']) && $chime['result']!=true) {
                                $txt.='<img style="position:relative;width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_X.png" />';
                            }
                            else $txt.='<img style="position:relative;width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_V.png" />';
                        }
                        else if ($chime['stato']=='escluso') $txt.='<img style="position:relative;width:18px;height:18px;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/avalon/img/call_R.png" />';
                    }
                }

            $txt.='</div>';

        $txt.='</div>';

        if ($row['cod_stato_commessa']!='CH' && $row['cod_stato_commessa']!='AP' ) {

            ob_start();
                $pratica->drawLine($row['rif'],'',true,true,'avalon');
            $txt.=ob_get_clean();
        }

        //!!!!!!! questo DIV non viene chiuso da questa funzione ma da quella chiamante !!!!!!!!!!
        $txt.='<div id="avalon_odl_block_'.$row['rif'].'" style="margin-top:5px;';
            if ($this->odlFlag && $row['cod_stato_commessa']!='CH' && $row['cod_stato_commessa']!='AP') $txt.='cursor:pointer;';
        $txt.='" ';
            if ($this->odlFlag && $row['cod_stato_commessa']!='CH' && $row['cod_stato_commessa']!='AP') $txt.='onclick="window[\'_nebulaApp_\'+window._nebulaApp.getTagFunzione()].openOdl(\''.$row['rif'].'\',\''.$m['dms'].'\');"';
        $txt.='>';

            $txt.='<div id="avalon_odl_block_nota_'.$row['rif'].'_" >';
            //$txt.='<div>';
                $txt.=$pratica->getNota($row['rif'],'');
            $txt.='</div>';

            if (isset($this->param['timeless']) || (in_array($row['cod_officina'],$this->odlFunc->getInterni()) && isset($row['intest_contratto']) && $row['intest_contratto']!="") ) {

                $txt.='<div>';

                    $txt.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;">';
                        $txt.='Contratto:';
                    $txt.='</div>';

                    $txt.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;line-height:20px;">';
                        $txt.=$row['numero_contratto'].' - '.($row['status_contratto']=='C'?'chiuso':'aperto');
                        if ($row['d_uscita']!="") {
                            $txt.=' - Consegnata: '.mainFunc::gab_todata($row['d_uscita']);
                        }
                    $txt.='</div>';

                $txt.='</div>';
            }

            $txt.='<div>';

                $txt.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:20px;font-size:1em;font-weight:bold;">';
                    $txt.=$row['mat_targa'];
                $txt.='</div>';

                $txt.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-weight:bold;line-height:20px;">';

                    if (isset($this->param['timeless']) || (in_array($row['cod_officina'],$this->odlFunc->getInterni()) && isset($row['intest_contratto']) && $row['intest_contratto']!="") ) {
                        $txt.=strtoupper(substr(utf8_encode($row['intest_contratto']),0,30));
                    }
                    else {
                        if ($row['util_ragsoc']!="") {
                            $txt.=strtoupper(substr(utf8_encode($row['util_ragsoc']),0,25));
                        }
                        else {
                            $txt.=strtoupper(substr(utf8_encode($row['intest_ragsoc']),0,25));
                        }
                    }
                $txt.='</div>';

            $txt.='</div>';

            $txt.='<div style="">';

                $txt.='<div style="position:relative;display:inline-block;width:27%;vertical-align:top;line-height:15px;font-size:0.9em;font-weight:normal;">';
                    $txt.='<div>('.$row['rif'].')'.substr($m['dms'],0,1).'</div>';
                    $txt.='<div>km: '.$row['km'].'</div>';
                $txt.='</div>';

                $txt.='<div style="position:relative;display:inline-block;width:73%;vertical-align:top;font-size:1em;font-weight:normal;line-height:15px;">';
                    $txt.='<div>'.$row['mat_telaio'].'</div>';
                    $txt.=strtolower(ucwords(substr($row['des_veicolo'],0,28)));
                $txt.='</div>';

            $txt.='</div>';

        //$txt.='</>';

        return $txt;
    }

    function drawLav() {

        echo '<div style="height:10%;" >';
        echo '</div>';

        echo '<div style="height:90%;overflow:scroll;" >';
            echo 'Lavorazioni';
        echo '</div>';

    }

    function graffaPDF($rifs) {

        $pdf=new graffaPDF('P','mm','A4');

        $m=array(
            "inizio"=>"201205",
            "fine"=>"210012",
            "dms"=>"",
            "piattaforma"=>"",
            "result"=>false
        );

        foreach ($rifs as $rif) {

            //$rif contiene il riferimento dell'odl ed il dms [rif,dms]
            $m["dms"]=$rif['dms'];
            $m["piattaforma"]=$this->wh->getPiattaforma($rif['dms']);
            $m['result']=false;
            
            //$this->wh->getOdl($rif);
            $m['result']=$this->odlFunc->getOdl($rif['rif'],$rif['dms'],'pre');

            //echo '<div>'.json_encode($m).'</div>';

            //foreach ($this->wh->exportMap() as $m) {

                if ($m['result']) {
                    $fid=$this->galileo->preFetchPiattaforma($m['piattaforma'],$m['result']);

                    $lam="";
                    $row=array();

                    while ($r=$this->galileo->getFetchPiattaforma($m['piattaforma'],$fid)) {

                        $row=$r;
                        $lam.=utf8_encode($row['des_riga'])." - ";
                    }

                    $pdf->AddPage();
                    $pdf->SetFont('Arial','B',15);
                    
                    $pdf->SetY(35);
                    $pdf->SetX(30);
                    //$pdf->Cell(150,8,"Appuntamento: ".mainFunc::gab_todata(substr($row['d_pren'],0,8))." - ".substr($row['d_pren'],9,5).' trasporto: '.$this->trasporto[$m['dms']][$row['cod_tipo_trasporto']]['testo'],0,0,'L');
                    $pdf->Cell(150,8,"Appuntamento: ".mainFunc::gab_todata(substr($row['d_pren'],0,8))." - ".substr($row['d_pren'],9,5).' trasporto: '.( ($temptra=$this->odlFunc->getTrasporto($row['cod_tipo_trasporto'],$m['dms']))?$temptra['testo']:''),0,0,'L');
                    $pdf->ln(10);
                    $pdf->SetX(30);

                    $pdf->Cell(150,8,"Riconsegna: ".mainFunc::gab_todata(substr($row['d_ricon'],0,8))." - ".substr($row['d_ricon'],9,5),0,0,'L');

                    $pdf->ln(10);
                    $pdf->SetX(30);
                    $pdf->Cell(150,8,'('.$row['rif'].')'.substr($m['dms'],0,1)." - ".($row['util_ragsoc']!=""?$row['util_ragsoc']:$row['intest_ragsoc']),0,0,'L');
                    $pdf->ln(10);
                    $pdf->SetX(30);
                    $pdf->Cell(150,8,$row['mat_targa']." - ".$row['mat_telaio'],0,0,'L');
                    $pdf->ln(10);
                    $pdf->SetX(30);
                    $pdf->SetFont('Arial','',15);
                    $pdf->Cell(150,8,$row['des_veicolo'],0,0,'L');
                    $pdf->ln(20);
                    $pdf->SetX(30);

                    //$pdf->MultiCell(150,8,iconv('UTF-8', 'windows-1252',$lam),0,'L');
                    $pdf->MultiCell(150,8,utf8_decode($lam),0,'L');

                }
            //}
        }

        echo base64_encode($pdf->Output('pdf','S'));
    }

}

?>