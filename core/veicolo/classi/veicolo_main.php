<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/core/chekko/chekko.php');

require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/concerto/concerto_veicoli.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/nebula/galileo/infinity/infinity_veicoli.php');

class nebulaVeicolo extends chekko {
    //gestisce tutte le necessità riferite all'elemento veicolo
    //viene chiamata per ogni operazione ma non viene sempre instanziata allo stesso modo
    //dipende dalle funzioni che servono al momento

    //identifica il dms primario per la gestione dei veicoli
    protected $map=array(
        "dms"=>"concerto"
    );

    //sono gli elementi di base di un veicolo
    protected $info=array(
        'rif'=>'',
        'telaio'=>'',
        'targa'=>'',
        'cod_marca'=>'',
        'des_marca'=>'',
        'modello'=>'',
        'des_veicolo'=>'',
        'cod_interno'=>'',
        'cod_esterno'=>'',
        'des_interno'=>'',
        'des_esterno'=>'',
        'cod_alim'=>'',
        'd_imm'=>'',
        'd_cons'=>'',
        'd_fine_gar'=>'',
        'd_rev'=>'',
        'des_note_off'=>'',
        'num_gestione'=>0,
        'd_gestione'=>'',
        'cod_natura_vendita'=>'',
        'mat_motore'=>'',
        'cod_ente_venditore'=>'',
        'cod_vw_tipo_veicolo'=>'',
        'des_vw_tipo_veicolo'=>'',
        'cod_po_tipo_veicolo'=>'',
        'des_po_tipo_veicolo'=>'',
        'anno_modello'=>'',
        'cod_infocar'=>'',
        'cod_infocar_anno'=>'',
        'cod_infocar_mese'=>'',
        'cod_marca_infocar'=>'',
        'dms'=>''
    );

    protected $galileo;

    function __construct($dms,$galileo) {

        parent::__construct('nebulaVeicolo');

        $this->galileo=clone($galileo);

        if ($dms!="") {
            $this->map['dms']=$dms;
            $this->info['dms']=$this->map['dms'];
        }

        $this->switchDms($this->map['dms']);

        $this->chk_fields=array(
            "rif"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "targa"=>array(
                "js_chk_vei_req"=>array("codice"=>1,"anor"=>"telaio","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "telaio"=>array(
                "js_chk_vei_req"=>array("codice"=>1,"anor"=>"targa","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cod_marca"=>array(
                "js_chk_vei_req"=>array("codice"=>1,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "modello"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "des_veicolo"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "mat_motore"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "cod_alim"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "d_imm"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "d_cons"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "d_rev"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),	
            "tipo_veicolo"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "note"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "infocar"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "infocar_anno"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            ),
            "infocar_mese"=>array(
                "js_chk_vei_req"=>array("codice"=>0,"anor"=>"","anand"=>"","anxor"=>""),
                "js_chk_vei_ifreq"=>array("campo"=>"","op"=>"","val"=>"")
            )
        );

        $this->tipi = array(
            "rif"=>"none",
            "targa"=>"tt",
            "telaio"=>"tt",
            "cod_marca"=>"none",
            "modello"=>"word",
            "des_veicolo"=>"none",
            "mat_motore"=>"word",
            "cod_alim"=>"none",
            "d_imm"=>"data",
            "d_cons"=>"data",
            "d_rev"=>"data",
            "tipo_veicolo"=>"none",
            "note"=>"note",
            "infocar"=>"none",
            "infocar_anno"=>"none",
            "infocar_mese"=>"none"
        );

        //dall'esterno occorre caricare EXPORT e CONV in base al DMS

        //09.11.2021 provo a non utilizzare "mappa" considerato che mi gioco in casa la scrittura del form
        
    }

    function switchDms($dms) {

        $this->map['dms']=$dms;
        
        if ($this->map['dms']=='concerto') {

            $obj=new galileoConcertoVeicoli();
            $nebulaDefault['veicoli']=array("maestro",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->info['dms']=$this->map['dms'];

            return true;
        }

        if ($this->map['dms']=='infinity') {

            $obj=new galileoInfinityVeicoli();
            $nebulaDefault['veicoli']=array("rocket",$obj);

            $this->galileo->setFunzioniDefault($nebulaDefault);

            $this->info['dms']=$this->map['dms'];

            return true;
        }

        return false;

    }

    function getInfo() {
        return $this->info;
    }

    //serve per le richieste dove non è specificato un telaio
    //viene passata la marca compatibile con il DMS specificato (contenuta in odlFunc) 
    function getDesModello($marca,$modello) {

        $res="";
        $this->galileo->executeGeneric('veicoli','getDesModello',array("marca"=>$marca,"modello"=>$modello),'');

        $fetID=$this->galileo->preFetch('veicoli');

        while($row=$this->galileo->getFetch('veicoli',$fetID)) {

            foreach ($this->info as $k=>$V) {
                $res=$row['descrizione'];
            }
        }

        return $res;
    }

    function loadVeicolo($rif) {

        $this->galileo->executeGeneric('veicoli','veiSelect',array("rif"=>$rif),'');

        $fetID=$this->galileo->preFetch('veicoli');

        while($row=$this->galileo->getFetch('veicoli',$fetID)) {

            foreach ($this->info as $k=>$V) {
                if (array_key_exists($k,$row)) {
                    $this->info[$k]=$row[$k];
                }
            }
        }
    }

    function loadTT($tt,$esatto) {

        //tt è un array targa,telaio,operazione
        $tt['esatto']=$esatto;

        $this->galileo->executeGeneric('veicoli','ttSelect',$tt,'');

        $fetID=$this->galileo->preFetch('veicoli');

        while($row=$this->galileo->getFetch('veicoli',$fetID)) {

            foreach ($this->info as $k=>$V) {
                if (array_key_exists($k,$row)) {
                    $this->info[$k]=$row[$k];
                }
            }
        }

        //diamo per scontato che la marca P è la medesima in entrambi i DMS
        //recupera il codice tempario Porsche da CONCERTO perché è l'unico posto dove c'è (07.03.2022)
        if ($this->info['cod_marca']=='P' &&  $this->info['dms']!='concerto') {

            $this->switchDms('concerto');

            $this->galileo->clearQuery();
            $this->galileo->clearQueryOggetto('default','veicoli');

            $wc="cod_veicolo='".$this->info['modello']."' AND des_model_year=".($this->info['anno_modello']!=""?"'".$this->info['anno_modello']."'":"(SELECT des_vw_modelyear FROM VE_ANAVEI WHERE num_rif_veicolo='".$this->info['rif']."')");

            $this->galileo->executeSelect('veicoli','TB_PIT_TEMPARIO_VEICOLI',$wc,"");

            $fetID=$this->galileo->preFetch('veicoli');

            while($row=$this->galileo->getFetch('veicoli',$fetID)) {
                $this->info['cod_po_tipo_veicolo']=$row['cod_id_gruppo'];
                $this->info['des_po_tipo_veicolo']=$row['des_veicolo'];
            }
        }

        //$this->log[]=$this->galileo->getLog('query');
    }

    function getLinks() {

        $res=array();

        $this->galileo->executeGeneric('veicoli','getLinker',array('codice'=>$this->info['rif']),"");

        $fetID=$this->galileo->preFetch('veicoli');

        while($row=$this->galileo->getFetch('veicoli',$fetID)) {

            //progressivo 1 è quello legato al veicolo in infinity
            //gli altri sono tutti 2 in infinity

            if ($row['progressivo']!='999') {
                $res[]=array(
                    "progressivo"=>$row['progressivo'],
                    "veicolo"=>$row['rif'],
                    "targa"=>$row['targa'],
                    "des_veicolo"=>$row['des_veicolo'],
                    "cod_anagra_util"=>$row['cod_anagra_util'],
                    "des_util"=>$row['ragsoc_util'],
                    "cod_anagra_intest"=>$row['cod_anagra_intest'],
                    "des_intest"=>$row['ragsoc_intest'],
                    "cod_anagra_locat"=>$row['cod_anagra_locat'],
                    "des_locat"=>$row['ragsoc_locat']
                );
            }
        }

        //$this->log[]=$this->galileo->getLog('query');

        return $res;
    }

    function draw_js() {

        echo 'window._js_chk_'.$this->form_tag.'.kind_tt=function(val,id) {';
            echo <<<JS
                val = val.toUpperCase();
                val.trim();
                this.chg_val(id,val);
                //se TRUE significa che sono stati trovati caratteri non validi
                var pattern=/[^A-Z0-9]/;
                return this.pattern.test(val);
JS;
        echo '};';
    }

    function draw_css() {}

    //////////////////////////////////////////////////////////////////////////////////

    function draw() {}

    function drawMain($edit) {

        $txt='<div style="position:relative;text-align:left;" >';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">'.substr($this->info['des_marca'],0,8).'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:23%;height:15px;vertical-align:top;font-weight:bold;">'.$this->info['targa'].'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:52%;height:15px;vertical-align:top;font-weight:bold;font-size:0.9em;">'.$this->info['telaio'].'</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">';
                    $txt.='<span style="margin-left:3px;">'.$this->info['rif'].'</span>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;width:23%;height:15px;vertical-align:top;font-size:0.9em;">'.$this->info['modello'].'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:52%;height:15px;vertical-align:top;font-size:0.9em;">'.substr(strtolower($this->info['des_veicolo']),0,26).'</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;margin-top:5px;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">consegna:</div>';
                $txt.='<div style="position:relative;display:inline-block;width:26%;height:15px;vertical-align:top;font-weight:bold;">'.mainFunc::gab_todata($this->info['d_cons']).'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:48%;height:15px;vertical-align:top;">tipo: '.$this->info['cod_vw_tipo_veicolo'].'</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;margin-top:5px;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:10px;vertical-align:top;font-size:0.9em;"></div>';
                $txt.='<div style="position:relative;display:inline-block;width:74%;height:10px;vertical-align:top;font-size:0.8em;">'.substr($this->info['des_vw_tipo_veicolo'],0,35).'</div>';
                
            $txt.='</div>';

        $txt.='</div>';

        //$txt.='<div>'.json_encode($this->galileo->getLog('query')).'</div>';

        return $txt;

    }

    /*function drawOdl($edit) {

        $txt=$this->drawMain($edit);

        $txt.='<div style="position:relative;text-align:left;margin-top:5px;" >';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">Km:</div>';

                if ($edit) {
                    $txt.='<div style="position:relative;display:inline-block;width:50%;height:15px;vertical-align:top;font-weight:bold;font-size:1em;">';
                        $txt.='<input id="odielleLinkerKm" type="text" style="width:90%;font-size:1.1em;font-weight:bold;" value="'.$this->info['km'].'" />';
                    $txt.='</div>';
                }
                else {
                    $txt.='<div style="position:relative;display:inline-block;width:50%;height:15px;vertical-align:top;font-weight:bold;font-size:1em;">'.$this->info['km'].'</div>';
                }

            $txt.='</div>';

        $txt.='</div>';

        return $txt;
    }*/

    function drawLinkerHead($row) {

        $txt='<div style="position:relative;text-align:left;background-color:#eeeeee;" >';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">'.substr($row['des_marca'],0,12).'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:23%;height:15px;vertical-align:top;font-weight:bold;">'.$row['targa'].'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:52%;height:15px;vertical-align:top;font-weight:bold;">'.$row['telaio'].'</div>';

            $txt.='</div>';

            $txt.='<div style="position:relative;" >';

                $txt.='<div style="position:relative;display:inline-block;width:25%;height:15px;vertical-align:top;font-size:0.9em;">';
                    $txt.='<img style="position:relative;width:13px;margin-left:2px;top:2px;cursor:pointer;" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/grabo.png" onclick="window._nebulaOdl.linker.setVeicolo(\''.$row['rif'].'\');" />';
                    $txt.='<span style="margin-left:3px;">'.$row['rif'].'</span>';
                $txt.='</div>';
                $txt.='<div style="position:relative;display:inline-block;width:23%;height:15px;vertical-align:top;font-size:0.9em;">'.$row['modello'].'</div>';
                $txt.='<div style="position:relative;display:inline-block;width:52%;height:15px;vertical-align:top;font-size:0.9em;">'.substr(strtolower($row['des_veicolo']),0,26).'</div>';

            $txt.='</div>';

        $txt.='</div>';

        return $txt;
    }

    function cercaLinker($param) {

        if (!$this->switchDms($param['dms'])) return;

        $ret=array(
            "records"=>0,
            "html"=>""
        );

        $first=true;
        $vei="";
        $link=array();

        $this->galileo->executeGeneric('veicoli','getLinker',$param,"");

        $fetID=$this->galileo->preFetch('veicoli');

        while($row=$this->galileo->getFetch('veicoli',$fetID)) {

            if ($vei!=$row['rif']) {

                $ret['records']++;

                if ($ret['records']>15) break;

                if (!$first) {
                    //$ret['html'].='<div id="linkerVeicoloLink_'.$row['rif'].'" data-info="'.base64_encode(json_encode($link)).'"></div>';
                    $ret['html'].='</div>';
                }

                //$ret['html'].='<div id="linkerVeicoloDiv_'.$row['rif'].'" style="width:93%;padding:2px;box-sizing:border-box;border-bottom:2px solid black;margin-top:4px;margin-bottom:4px;" data-info="'.base64_encode(json_encode($row)).'">';
                $ret['html'].='<div id="linkerVeicoloDiv_'.$row['rif'].'" style="width:93%;padding:2px;box-sizing:border-box;border-bottom:2px solid black;margin-top:4px;margin-bottom:4px;" data-info="">';

                //scrittura del DIV dei dati della vettura in questo contesto
                $ret['html'].=$this->drawLinkerHead($row);

                $vei=$row['rif'];

                $link=array();
            }

            //scrittura del div dell'abbinamento se esiste
            if ($row['progressivo']!='999') {

                $link[$row['progressivo']]=$row;

                if (count($link)==1) {

                    $ret['html'].='<div style="position:relative;margin-top:3px;border:1px solid black;padding:2px;box-sizing:border-box;background-color:beige;">';
                    
                        if ($row['cod_anagra_util']!='') $ret['html'].='<div style="font-weight:bold;">'.substr($row['ragsoc_util'],0,30).'</div>';

                        if ($row['cod_anagra_locat']!='') $ret['html'].='<div style="font-size:0.8em;">'.substr($row['ragsoc_locat'],0,35).'</div>';
                        elseif ($row['cod_anagra_intest']!='') $ret['html'].='<div style="font-size:0.8em;">'.substr($row['ragsoc_intest'],0,35).'</div>';

                        if ($row['cod_anagra_util']!='') $ret['html'].='<img style="position:absolute;top:50%;right:2px;width:20px;height:15px;cursor:pointer;transform:translate(0px,-50%);" data-info="'.base64_encode(json_encode($row)).'" src="http://'.$_SERVER['SERVER_ADDR'].'/nebula/core/odl/img/link.png" onclick="window._nebulaOdl.linker.setAbbinamentoByVeicolo(this);" />';

                    $ret['html'].='</div>';
                }
            }

            $first=false;
        }

        if (!$first) {
            //$ret['html'].='<div id="linkerVeicoloLink_'.$vei.'" data-info="'.base64_encode(json_encode($link)).'"></div>';
            $ret['html'].='</div>';
        }

        //$ret['html']=json_encode($this->galileo->getLog('query'));

        return $ret;
    }



}
?>