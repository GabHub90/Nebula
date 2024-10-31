<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/divo/divo.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/gab500/galileo_comest.php');
require_once('commessa_pdf.php');

class nebulaComest {

    protected $actualVersione=1;
    protected $actualRevisione=0;

    protected $flagRighe=false;

    protected $info=array(
        "rif"=>"",
        "versione"=>"",
        "targa"=>"",
        "telaio"=>"",
        "descrizione"=>"",
        "dms"=>"",
        "odl"=>"",
        "fornitore"=>array(),
        "d_apertura"=>"",
        "utente_apertura"=>"",
        "d_annullo"=>"",
        "utente_annullo"=>"",
        "controllo"=>"",
        "utente_controllo"=>"",
        "d_controllo"=>""
    );

    protected $revisioni=array();

    protected $fornitori=array();

    protected $danni=array();

    protected $operazioni=array();

    protected $controllo=array();

    //definizione di nuovi metodi in maniera dinamica
    protected $methods = array();
    protected $closure = array();

    protected $log=array();

    protected $galileo;

    function __construct($galileo) {

        //inizializzazione di Galileo con il DB di NEBULA
        $obj=new galileoComest();
        $nebulaDefault['comest']=array("gab500",$obj);

        $galileo->setFunzioniDefault($nebulaDefault);

        $this->galileo=$galileo;

        //lettura dal DB della versione attuale
        $galileo->executeGeneric('comest',"getVersione",array(),'');
        if ($galileo->getResult()) {
            $fid=$galileo->preFetch('comest');

            while ($row=$galileo->getFetch("comest",$fid)) {
                $this->actualVersione=$row['versione'];
            }
        }

        $galileo->clearQuery();
        $galileo->clearQueryOggetto('default','comest');

        //lettura dei fornitori

        $galileo->executeSelect('comest',"COMEST_fornitori",'','ragsoc');
        if ($galileo->getResult()) {
            $fid=$galileo->preFetch('comest');

            while ($row=$galileo->getFetch("comest",$fid)) {
                $row['ragsoc']=mb_convert_encoding($row['ragsoc'], 'UTF-8', 'ISO-8859-1');
                $row['indirizzo']=mb_convert_encoding($row['indirizzo'], 'UTF-8', 'ISO-8859-1');
                $this->fornitori[$row['ID']]=$row;
            }
        }


        /*TEST
        $this->fornitori=array(
            "1"=>array(
                "ID"=>"1",
                "ragsoc"=>"Augusto Gabellini Srl",
                "indirizzo"=>"Str. Romagna, 121 Pesaro 61121 (PU)",
                "mail"=>"michele.binda@gabellini.it",
                "tel1"=>"0721270364",
                "nota1"=>"Responsabile",
                "tel2"=>"0721279325",
                "nota2"=>"Centralino"
            ),
            "2"=>array(
                "ID"=>"2",
                "ragsoc"=>"Autocarrozzeria Luchetti di Luchetti Leonardo & C. Snc",
                "indirizzo"=>"Via Umbria, 24 Pesaro 61122 (PU)",
                "mail"=>"",
                "tel1"=>"0721410829",
                "nota1"=>"",
                "tel2"=>"3393131555",
                "nota2"=>"Diego"
            )
        );
        */

    }

    //definisce in maniera dinamica nuovi metodi
    //serve per scrivere il metodo DRAW in base alla versione del modulo
    public function __call($methodName, array $args) {

        if (isset($this->methods[$methodName])) {
            return call_user_func_array($this->methods[$methodName], $args);
        }
    }

    function set_closure() {
        foreach ($this->closure as $key=>$c) {
            $this->methods[$key] = Closure::bind($c, $this, get_class());
        }
    }

    function getLog() {
        return $this->log;
    }

    function getVersione() {
        return $this->actualVersione;
    }

    function getControllo() {
        return $this->controllo;
    }

    function init($a) {

        foreach ($a as $k=>$v) {
            if (array_key_exists($k,$this->info)) $this->info[$k]=$v;
        }

        if ($this->info['rif']!="") {

            $this->galileo->setResult(false);
            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','comest');

            $rows=0;
            
            $this->galileo->executeSelect('comest','COMEST_commesse',"rif='".$this->info['rif']."'",'');
            
            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('comest');

                while($row=$this->galileo->getFetch('comest',$fid)) {
                    $this->info['versione']=$row['versione'];
                    $this->info['targa']=$row['targa'];
                    $this->info['telaio']=$row['telaio'];
                    $this->info['descrizione']=$row['descrizione'];
                    $this->info['dms']=$row['dms'];
                    $this->info['odl']=$row['odl'];
                    $this->info['fornitore']=$row['fornitore']==""?array():json_decode($row['fornitore'],true);
                    $this->info['d_apertura']=$row['d_apertura'];
                    $this->info['utente_apertura']=$row['utente_apertura'];
                    $this->info['d_annullo']=$row['d_annullo'];
                    $this->info['utente_annullo']=$row['utente_annullo'];
                    $this->info['controllo']=$row['controllo'];
                    $this->info['utente_controllo']=$row['utente_controllo'];
                    $this->info['d_controllo']=$row['d_controllo'];

                    $rows++;
                }
            }
            else die('Commessa non trovata!!!');

            if ($rows==0) die('Commessa non trovata!!!');

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','comest');

            $this->galileo->executeSelect('comest','COMEST_revisioni',"commessa='".$this->info['rif']."'",'revisione');
            
            if ($this->galileo->getResult()) {
                $fid=$this->galileo->preFetch('comest');

                while($row=$this->galileo->getFetch('comest',$fid)) {
                    $this->revisioni[$row['revisione']]=$row;
                    $this->revisioni[$row['revisione']]['righe']=array();

                    $this->actualRevisione=$row['revisione'];

                    $this->galileo->clearQuery();
                    $this->galileo->clearQueryOggetto('default','comest');

                    $this->galileo->executeSelect('comest',"COMEST_lavorazioni","commessa='".$this->info['rif']."' AND revisione='".$row['revisione']."'","ID");

                    $fid2=$this->galileo->preFetch('comest');

                    while($row2=$this->galileo->getFetch('comest',$fid2)) {

                        $this->revisioni[$row['revisione']]['righe'][$row2['ID']]=$row2;

                        //se la revisione non è confermata segnala se ci sono delle righe di lavorazione
                        if ($row['d_chiusura']=="") $this->flagRighe=true;
                    }
                }
            }

            //TEST
            /*$this->revisioni[1]=array(
                "d_creazione"=>'20230316',
                "d_chiusura"=>'',
                "righe"=>array(),
                "preventivo"=>0,
                "riconsegna"=>"",
                "nota"=>"",
                "check"=>array()
            );*/
            //END TEST
        }

        else {
            $this->info['versione']=$this->actualVersione;
        }

        include($_SERVER['DOCUMENT_ROOT'].'/nebula/apps/comest/classi/versioni/versione_'.$this->info['versione'].'.php');

        $this->set_closure();
    }

    function drawHead($edit) {

        echo '<div style="position:relative;height:98%;width:100%;border:1px solid black;background-color:#dddddd;padding 3px;box-sizing:border-box;font-size:11pt;" >';

            echo '<div style="position:relative;" >';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Targa</div>';
                    echo '<div style="font-size:1.2em;">'.strtoupper($this->info['targa']).'</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:22%;text-align:center;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Telaio</div>';
                    echo '<div style="font-size:1.2em;">'.strtoupper($this->info['telaio']).'</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:center;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Descrizione</div>';
                    echo '<div style="font-size:1.2em;">'.substr($this->info['descrizione'],0,35).'</div>';
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:18%;text-align:center;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">OdL</div>';
                    if ($this->info['odl']!=0) {
                        echo '<div style="font-size:1.2em;">('.substr($this->info['dms'],0,1).') '.$this->info['odl'].'</div>';
                    }
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:5%;text-align:center;" >';
                    if ($this->info['d_controllo']=='' && $this->info['d_annullo']=='') {
                        echo '<img style="width:25px;height:25px;margin-top:5px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/trash.png" onclick="window._nebulaComest.annullaCommessa();" />';
                    }
                    else if ($this->info['d_annullo']!='') {
                        echo '<div style="position:relative;border:2px solid red;box-sizing:border-box;text-align:center;font-size:0.7em;color:red;margin-top:10px;">Annullata</div>';
                    }
                echo '</div>';
                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:10%;text-align:center;" >';
                    echo '<div style="font-weight:bold;font-size:0.9em;">Commessa</div>';
                    echo '<div style="font-size:1.2em;">'.$this->info['rif'].'</div>';
                echo '</div>';
            echo '</div>';

            echo '<div style="position:relative;margin-top: 3px;font-size: 1.2em;padding: 3px;box-sizing: border-box;" >';

                echo '<div style="position:relative;display:inline-block;vertical-align:top;width:55%;text-align:left;" >';
                    if ($edit) {
                        echo '<select id="comest_fornitore" style="width:95%;font-size:1.2em;" onchange="window._nebulaComest.editFornitore(this.value);" >';
                            echo '<option value="0">Scegli un fornitore...</option>';
                            foreach ($this->fornitori as $k=>$f) {
                                echo '<option value="'.$k.'" '.(isset($this->info['fornitore']['ID']) && $this->info['fornitore']['ID']==$k?'selected':'').' >'.$f['ragsoc'].'</option>';
                            }
                        echo '</select>';
                    }
                    else {
                        echo '<div style="position:relative;width:95%;border:1px solid black;box-sizing:border-box;padding:2px;font-weight:bold;">'.$this->info['fornitore']['ragsoc'].'</div>';
                    }
                echo '</div>';

                echo '<div id="comest_indirizzo" style="position:relative;display:inline-block;vertical-align:top;width:45%;text-align:left;" >';
                    if (isset($this->info['fornitore']['indirizzo'])) echo $this->info['fornitore']['indirizzo'];
                echo '</div>';

            echo '</div>';

            echo '<div style="position:relative;margin-top: 3px;font-size: 1.2em;padding: 3px;box-sizing: border-box;" >';

                echo '<div id="comest_mail" style="position:relative;display:inline-block;vertical-align:top;width:40%;text-align:left;" >';
                    if (isset($this->info['fornitore']['mail'])) echo $this->info['fornitore']['mail'];
                echo '</div>';

                echo '<div id="comest_tel1" style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;" >';
                    if (isset($this->info['fornitore']['tel1'])) echo $this->info['fornitore']['tel1'].($this->info['fornitore']['nota1']!=""?' ('.$this->info['fornitore']['nota1'].')':'');
                echo '</div>';

                echo '<div id="comest_tel2" style="position:relative;display:inline-block;vertical-align:top;width:30%;text-align:left;" >';
                    if (isset($this->info['fornitore']['tel2'])) echo $this->info['fornitore']['tel2'].($this->info['fornitore']['nota2']!=""?' ('.$this->info['fornitore']['nota2'].')':'');
                echo '</div>';

            echo '</div>';

        echo '</div>';

    }

    function drawJS() {

        echo '<script style="text/javascript" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/core/comest.js?v='.time().'" ></script>';
        echo '<script style="text/javascript">';
                echo 'window._nebulaComest=new nebulaComest();';
        echo '</script>';
    }
        
    function draw() {

        //new identifica una commessa non ancora aperta
        //edit una commessa non ancora confermata (fornitore ancora modificabile)
        $new=$this->info['rif']==""?true:false;
        $edit=$this->info['d_apertura']==""?true:false;

        $divo=new Divo('comest','14%','85%',true);

        $divo->setBk('#e7a5f1');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.1em",
            "margin-left"=>"20px",
            "margin-top"=>"-2px"
        );

        $css2=array(
            "width"=>"15px",
            "height"=>"15px",
            "top"=>"50%",
            "transform"=>"translate(0%,-50%)",
            "right"=>"5px"
        );

        $divo->setChkimgCss($css2);

        ob_start();
            $this->drawHead($edit);
        $divo->add_div('Testata','black',0,'',ob_get_clean(),($this->flagRighe?0:1),$css);

        ob_start();
            $this->drawDanni();
        $divo->add_div('Danni','black',1,'Y',ob_get_clean(),($this->flagRighe?1:0),$css);

        //$divo->add_div('Interno','black',1,'Y',$txt,0,$css);

        ////////////////////////////////////////////////////////////////////

        echo '<div style="position:relative;width:100%;height:28%;" >';

            $divo->build();
            $divo->draw();

        echo '</div>';

        echo '<div id="nebula_comest_body" style="position:relative;width:100%;height:72%;overflow:scroll;overflow-x:hidden;">';

            if ($this->info['rif']=="") {

                echo '<script style="text/javascript">';
                    echo 'window._nebulaComest.setFornitori(\''.base64_encode(json_encode($this->fornitori)).'\');'; 
                    echo 'window._nebulaComest.setCommessa(\''.base64_encode(json_encode($this->info)).'\');';
                    echo 'window._nebulaComest.setRevisioni(\''.base64_encode(json_encode($this->revisioni)).'\');';
                echo '</script>';

                echo '<div style="position:relative;width:100%;text-align:center;" >';
                    echo '<button style="font-size:1.2em;" onclick="window._nebulaComest.apriCommessa();" >Apri nuova commessa per il veicolo selezionato</button>';
                echo '</div>';
            }
            else {
                $this->drawBody();
            }

        echo '</div>';
        
    }

    function drawBody() {

        if (count($this->revisioni)==0) {
            echo '<div style="color:red;font-weight:bold;" >ERRORE: Non ci sono revisioni!!!</div>';
            return;
        }

        /*if ($this->info['d_annullo']!="") {
           echo '<div style="position:relative;width:100%;margin-top:10px;margin-bottom:10px;color:red;font-weight:bold;text-align:center;">Commessa ANNULLATA </div>';
        }*/

        echo '<script style="text/javascript">';
            echo 'window._nebulaComest.setFornitori(\''.base64_encode(json_encode($this->fornitori)).'\');';  
            echo 'window._nebulaComest.setCommessa(\''.base64_encode(json_encode($this->info)).'\');';
            echo 'window._nebulaComest.setRevisioni(\''.base64_encode(json_encode($this->revisioni)).'\');';
            echo 'window._nebulaComest.setDanni(\''.base64_encode(json_encode($this->danni)).'\');';
            echo 'window._nebulaComest.setOperazioni(\''.base64_encode(json_encode($this->operazioni)).'\');';
        echo '</script>';

        $divo2=new Divo('comRev','5%','94%',true);

        $divo2->setBk('#a5a7f1');

        $css=array(
            "font-weight"=>"bold",
            "font-size"=>"1.1em",
            "margin-left"=>"20px",
            "margin-top"=>"-2px"
        );

        foreach ($this->revisioni as $k=>$r) {

            $txt='<div id="comest_revisione_'.$k.'" style="width:100%;height:100%;overflow:scroll;overflow-x:hidden;" ></div>';

            $divo2->add_div($k,'black',0,'',$txt,($k==$this->actualRevisione?1:0),$css);

        }

        $divo2->build();
        $divo2->draw();

        echo '<script style="text/javascript">';
            echo 'window._nebulaComest.drawBody();';
        echo '</script>';
    }

    function pdf() {

        $pdf=new comestPDF('P','mm','A4');

        $pdf->AddPage();

        $pdf->drawCommessa($this->info,$this->revisioni[$this->actualRevisione],false);

        return $pdf->exportCommessa_b64();
    }

    function bozzaPdf() {

        $pdf=new comestPDF('P','mm','A4');

        $pdf->AddPage();

        $pdf->drawCommessa($this->info,$this->revisioni[$this->actualRevisione],true);

        return $pdf->exportCommessa_b64();
    }

    function logMail($a) {

        $this->galileo->executeInsert('comest','COMEST_logmail',$a);
    }

    function allineaChiuse() {

        $this->galileo->clearQuery();
        $this->galileo->clearQueryOggetto('default','comest');

        $this->galileo->executeGeneric('comest',"getAllinea",array(),'');

        if ($this->galileo->getResult()) {
            $fid=$this->galileo->preFetch('comest');

            while ($row=$this->galileo->getFetch("comest",$fid)) {

                $delta=mainFunc::gab_delta_tempo($row['riconsegna'],$row['d_controllo'],'g');

                if ($delta>2) {
                    $a=array(
                        "d_controllo"=>$row['riconsegna']
                    );

                    $this->galileo->clearQuery();
                    $this->galileo->clearQueryOggetto('default','comest');

                    $this->galileo->executeUpdate('comest','COMEST_commesse',$a,"rif='".$row['rif']."'");

                    $this->log[]=$row;
                }
                
            }
        }
    }

    /*function drawDanni() {

        //#####################################
        //modificare in base alla versione in info['versione] (INCLUDE)
        //#####################################

        $this->operazioni=array(
            "10"=>array(
                "tag"=>"Sollevare Bozza",
                "txt"=>"Sollevare Bozza",
                "default"=>0
            ),
            "20"=>array(
                "tag"=>"Ritocco a Pennello",
                "txt"=>"Ritocco a Pennello",
                "default"=>0,
            ),
            "30"=>array(
                "tag"=>"Riverniciare",
                "txt"=>"Riverniciare",
                "default"=>1,
            ),
            "40"=>array(
                "tag"=>"Riparare e Riverniciare",
                "txt"=>"Riparare e Riverniciare",
                "default"=>0
            ),
            "50"=>array(
                "tag"=>"Sostituire",
                "txt"=>"Sostituire",
                "default"=>0
            ),
            "60"=>array(
                "tag"=>"Riparazione Grandine",
                "txt"=>"Riparazione Grandine",
                "default"=>0
            ),
            "70"=>array(
                "tag"=>"Lucidatura",
                "txt"=>"Lucidatura",
                "default"=>0
            ),
            "80"=>array(
                "tag"=>"PARTI CORRELATE",
                "txt"=>"",
                "default"=>0
            )
        );

        $defColor="#ffffffcc;";

        $this->danni=array(
            "1"=>array(
                "tag"=>"Vettura completa",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "10"=>array(
                "tag"=>"Paraurti Anteriore",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "11"=>array(
                "tag"=>"Spoiler Anteriore",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "12"=>array(
                "tag"=>"Fanale Ant. Dx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "13"=>array(
                "tag"=>"Fanale Ant. Sx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "20"=>array(
                "tag"=>"Cofano",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "21"=>array(
                "tag"=>"Parabrezza",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "22"=>array(
                "tag"=>"Tetto",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "30"=>array(
                "tag"=>"Parafango Ant. Sx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "31"=>array(
                "tag"=>"Cerchio Ant. Sx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "40"=>array(
                "tag"=>"Porta Ant. sx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "41"=>array(
                "tag"=>"Vetro Porta Ant. sx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "42"=>array(
                "tag"=>"Calotta Sx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "43"=>array(
                "tag"=>"Porta Post. sx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "44"=>array(
                "tag"=>"Vetro Porta Post. sx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "45"=>array(
                "tag"=>"Sottoporta Sx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "50"=>array(
                "tag"=>"Parafango Post. Sx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "51"=>array(
                "tag"=>"Cerchio Post. Sx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "60"=>array(
                "tag"=>"Paraurti Posteriore",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "61"=>array(
                "tag"=>"Spoiler Posteriore",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "62"=>array(
                "tag"=>"Fanale Post. Sx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "63"=>array(
                "tag"=>"Fanale Post. Dx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "70"=>array(
                "tag"=>"Portellone",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "71"=>array(
                "tag"=>"Lunotto",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "72"=>array(
                "tag"=>"Spoiler Tetto",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "80"=>array(
                "tag"=>"Parafango Post. Dx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "81"=>array(
                "tag"=>"Cerchio Post. Dx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "90"=>array(
                "tag"=>"Porta Post. Dx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "91"=>array(
                "tag"=>"Vetro Porta Post. Dx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "92"=>array(
                "tag"=>"Porta Ant. Dx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "93"=>array(
                "tag"=>"Vetro Porta Ant. Dx",
                "color"=>"#30f5becc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "94"=>array(
                "tag"=>"Calotta Dx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "95"=>array(
                "tag"=>"Sottoporta Dx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "100"=>array(
                "tag"=>"Parafango Ant. Dx",
                "color"=>"#ffe000dd;",
                "defcolor"=>$defColor,
                "set"=>0
            ),
            "101"=>array(
                "tag"=>"Cerchio Ant. Dx",
                "color"=>"#ff7bfbcc;",
                "defcolor"=>$defColor,
                "set"=>0
            )
        );

        echo '<div style="position:relative;display:inline-block;width:60px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
            echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/muso.png" />';

            echo '<div id="comest_spot_11" data-color="'.$this->danni['11']['color'].'" data-defcolor="'.$this->danni['11']['defcolor'].'" style="position:absolute;top:30%;left:1%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['11']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'11\');" ></div>';
            echo '<div id="comest_spot_10" data-color="'.$this->danni['10']['color'].'" data-defcolor="'.$this->danni['10']['defcolor'].'" style="position:absolute;bottom:30%;right:1%;transform:translate(0,+50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['10']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'10\');" ></div>';        
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
            echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/pianta.png" />';

            echo '<div id="comest_spot_12" data-color="'.$this->danni['12']['color'].'" data-defcolor="'.$this->danni['12']['defcolor'].'" style="position:absolute;top:1%;left:8%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['12']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'12\');" ></div>';
            echo '<div id="comest_spot_13" data-color="'.$this->danni['13']['color'].'" data-defcolor="'.$this->danni['13']['defcolor'].'" style="position:absolute;bottom:1%;left:8%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['13']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'13\');" ></div>';
            echo '<div id="comest_spot_20" data-color="'.$this->danni['20']['color'].'" data-defcolor="'.$this->danni['20']['defcolor'].'" style="position:absolute;top:50%;left:13%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['20']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'20\');" ></div>';
            echo '<div id="comest_spot_94" data-color="'.$this->danni['94']['color'].'" data-defcolor="'.$this->danni['94']['defcolor'].'" style="position:absolute;top:1%;left:34%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['94']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'94\');" ></div>';
            echo '<div id="comest_spot_42" data-color="'.$this->danni['42']['color'].'" data-defcolor="'.$this->danni['42']['defcolor'].'" style="position:absolute;bottom:1%;left:34%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['42']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'42\');" ></div>';
            echo '<div id="comest_spot_21" data-color="'.$this->danni['21']['color'].'" data-defcolor="'.$this->danni['21']['defcolor'].'" style="position:absolute;top:50%;left:29%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['21']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'21\');" ></div>';

            echo '<div id="comest_spot_22" data-color="'.$this->danni['22']['color'].'" data-defcolor="'.$this->danni['22']['defcolor'].'" style="position:absolute;top:50%;left:52%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['22']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'22\');" ></div>';
            echo '<div id="comest_spot_72" data-color="'.$this->danni['72']['color'].'" data-defcolor="'.$this->danni['72']['defcolor'].'" style="position:absolute;top:50%;left:70%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['72']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'72\');" ></div>';
            echo '<div id="comest_spot_71" data-color="'.$this->danni['71']['color'].'" data-defcolor="'.$this->danni['71']['defcolor'].'" style="position:absolute;top:50%;left:85%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['71']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'71\');" ></div>';
        echo '</div>';

         echo '<div style="position:relative;display:inline-block;width:80px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
            echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/coda.png" />';

            echo '<div id="comest_spot_63" data-color="'.$this->danni['63']['color'].'" data-defcolor="'.$this->danni['63']['defcolor'].'" style="position:absolute;top:1%;left:5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['63']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'63\');" ></div>';
            echo '<div id="comest_spot_62" data-color="'.$this->danni['62']['color'].'" data-defcolor="'.$this->danni['62']['defcolor'].'" style="position:absolute;bottom:1%;left:5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['62']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'62\');" ></div>';
            echo '<div id="comest_spot_70" data-color="'.$this->danni['70']['color'].'" data-defcolor="'.$this->danni['70']['defcolor'].'" style="position:absolute;top:50%;left:1%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['70']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'70\');" ></div>';

            echo '<div id="comest_spot_60" data-color="'.$this->danni['60']['color'].'" data-defcolor="'.$this->danni['60']['defcolor'].'" style="position:absolute;top:30%;right:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['60']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'60\');" ></div>';
            echo '<div id="comest_spot_61" data-color="'.$this->danni['61']['color'].'" data-defcolor="'.$this->danni['61']['defcolor'].'" style="position:absolute;bottom:30%;right:1%;transform:translate(0,+50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['61']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'61\');" ></div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
            echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/fiancosx.png" />';

            echo '<div id="comest_spot_30" data-color="'.$this->danni['30']['color'].'" data-defcolor="'.$this->danni['30']['defcolor'].'" style="position:absolute;top:45%;left:17.5%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['30']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'30\');" ></div>';
            echo '<div id="comest_spot_31" data-color="'.$this->danni['31']['color'].'" data-defcolor="'.$this->danni['31']['defcolor'].'" style="position:absolute;bottom:10%;left:17.5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['31']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'31\');" ></div>';

            echo '<div id="comest_spot_41" data-color="'.$this->danni['41']['color'].'" data-defcolor="'.$this->danni['41']['defcolor'].'" style="position:absolute;top:20%;left:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['41']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'41\');" ></div>';
            echo '<div id="comest_spot_40" data-color="'.$this->danni['40']['color'].'" data-defcolor="'.$this->danni['40']['defcolor'].'" style="position:absolute;top:60%;left:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['40']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'40\');" ></div>';
            echo '<div id="comest_spot_44" data-color="'.$this->danni['44']['color'].'" data-defcolor="'.$this->danni['44']['defcolor'].'" style="position:absolute;top:20%;left:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['44']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'44\');" ></div>';
            echo '<div id="comest_spot_43" data-color="'.$this->danni['43']['color'].'" data-defcolor="'.$this->danni['43']['defcolor'].'" style="position:absolute;top:60%;left:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['43']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'43\');" ></div>';
            echo '<div id="comest_spot_45" data-color="'.$this->danni['45']['color'].'" data-defcolor="'.$this->danni['45']['defcolor'].'" style="position:absolute;bottom:1%;left:54%;transform:translate(-50%,0);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['45']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'45\');" ></div>';
            echo '<div id="comest_spot_50" data-color="'.$this->danni['50']['color'].'" data-defcolor="'.$this->danni['50']['defcolor'].'" style="position:absolute;top:45%;right:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['50']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'50\');" ></div>';
            echo '<div id="comest_spot_51" data-color="'.$this->danni['51']['color'].'" data-defcolor="'.$this->danni['51']['defcolor'].'" style="position:absolute;bottom:10%;right:16%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['51']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'51\');" ></div>';
        echo '</div>';

        echo '<div style="position:relative;display:inline-block;width:320px;height:120px;border:1px solid #cccccc;box-sizing:border-box;margin-top:5px;margin-left:5px;">';
            echo '<img style="position:absolute;top:0px;left:0px;width:100%;height:100%;z-index:1;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/apps/comest/img/fiancodx.png" />';

            echo '<div id="comest_spot_100" data-color="'.$this->danni['100']['color'].'" data-defcolor="'.$this->danni['100']['defcolor'].'" style="position:absolute;top:45%;right:17.5%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['100']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'100\');" ></div>';
            echo '<div id="comest_spot_101" data-color="'.$this->danni['101']['color'].'" data-defcolor="'.$this->danni['101']['defcolor'].'" style="position:absolute;bottom:10%;right:17.5%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['101']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'101\');" ></div>';
            echo '<div id="comest_spot_93" data-color="'.$this->danni['93']['color'].'" data-defcolor="'.$this->danni['93']['defcolor'].'" style="position:absolute;top:20%;right:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['93']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'93\');" ></div>';
            echo '<div id="comest_spot_92" data-color="'.$this->danni['92']['color'].'" data-defcolor="'.$this->danni['92']['defcolor'].'" style="position:absolute;top:60%;right:40%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['92']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'92\');" ></div>';
            echo '<div id="comest_spot_91" data-color="'.$this->danni['91']['color'].'" data-defcolor="'.$this->danni['91']['defcolor'].'" style="position:absolute;top:20%;right:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['91']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'91\');" ></div>';
            echo '<div id="comest_spot_90" data-color="'.$this->danni['90']['color'].'" data-defcolor="'.$this->danni['90']['defcolor'].'" style="position:absolute;top:60%;right:61%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['90']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'90\');" ></div>';     
            echo '<div id="comest_spot_95" data-color="'.$this->danni['95']['color'].'" data-defcolor="'.$this->danni['95']['defcolor'].'" style="position:absolute;bottom:1%;right:46%;transform:translate(-50%,0);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['95']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'95\');" ></div>';
            echo '<div id="comest_spot_80" data-color="'.$this->danni['80']['color'].'" data-defcolor="'.$this->danni['80']['defcolor'].'" style="position:absolute;top:45%;left:10%;transform:translate(0,-50%);height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['80']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'80\');" ></div>';
            echo '<div id="comest_spot_81" data-color="'.$this->danni['81']['color'].'" data-defcolor="'.$this->danni['81']['defcolor'].'" style="position:absolute;bottom:10%;left:16%;height:16%;aspect-ratio:1;border:3px solid black;border-radius:50%;z-index:5;background-color: '.$this->danni['81']['defcolor'].'" onclick="window._nebulaComest.clickSpot(\'81\');" ></div>';
        echo '</div>';

    }*/

}

?>